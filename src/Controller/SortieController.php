<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Status;
use App\Entity\User;
use App\Form\SortieCancellationType;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Repository\StatusRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

#[Route('/sortie', name: 'sortie')]
final class SortieController extends AbstractController
{
//    SLB : modif de la fonction pour que l'affichage par défaut, sans utiliser de filtre, exclue les sorties archivées de la vue
    #[Route('', name: '_list')]
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {
        // Récupère l'utilisateur connecté, ou null si non connecté
        $user = $this->getUser();

        if ($user) {
            // Si l'utilisateur est connecté, on procède avec les filtres
            $form = $this->createForm(SortieFilterType::class);
            $form->handleRequest($request);

            // Initialise les filtres avec un tableau vide
            $filters = [];

            if ($form->isSubmitted() && $form->isValid()) {
                $filters = $form->getData();
            }

            // Récupère les sorties en fonction des filtres et de l'utilisateur connecté
            $sorties = $sortieRepository->findFilteredFromForm($filters, $user);
        } else {
            // Si l'utilisateur n'est pas connecté, on affiche toutes les sorties
            $sorties = $sortieRepository->findAll();
            $form = $this->createForm(SortieFilterType::class); // Crée le formulaire pour l'affichage, même si non utilisé
        }

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/create', name: '_create')]
    public function create(
        EntityManagerInterface $em,
        Request $request,
        StatusRepository $statusRepository // Injection du repository de statut
    ): Response
    {
        // Logique pour créer une sortie
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupère le statut "Créée" depuis la base de données
            $createdStatus = $statusRepository->findOneBy(['status_label' => 'Créée']);

            if (!$createdStatus) {
                throw new \Exception('Le statut par défaut "Créée" n\'a pas été trouvé.');
            }

            // Assigne le statut à la sortie
            $sortie->setStatus($createdStatus);

            // Assigne l'utilisateur courant comme organisateur
            /** @var User $user */
            $user = $this->getUser();
            $sortie->setOrganisator($user);

            $em->persist($sortie);

            $em->flush();

            $this->addFlash('success', 'Sortie créé avec succès !');

            return $this->redirectToRoute('_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/create.html.twig', [
            'sortie_form' => $form,
        ]);
    }

    #[Route('/{id}', name: '_detail')]
    public function detail(Sortie $sortie): Response
    {
        $user = $this->getUser();
        $isOrganisator = $user && $user->getId() === $sortie->getOrganisator()->getId();
        $isRegistered = $user && $sortie->getUsers()->contains($user);
        $isFull = count($sortie->getUsers()) >= $sortie->getMaxRegistrations();
        $isRegistrationOpen = $sortie->getStatus()->getStatusLabel() === 'Ouverte' && new \DateTime() < $sortie->getRegistrationDeadline();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'is_organisator' => $isOrganisator,
            'is_registered' => $isRegistered,
            'is_full' => $isFull,
            'is_registration_open' => $isRegistrationOpen,
        ]);
    }
    #[Route('/{id}/register', name: '_register')]
    public function register(Sortie $sortie, EntityManagerInterface $em, MailService $mailService): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $erreur = $this->registerConditionsVerified($sortie, $user);

        if ($erreur) {
            $this->addFlash('error', $erreur);
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $sortie->addUser($user);
        $em->flush();

        $mailService->sendInscriptionMail($user, $sortie);

        $this->addFlash('success', 'Inscription réussie !');
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }

    private function registerConditionsVerified(Sortie $sortie, User $user): ?string
    {
        $now = new \DateTime();

        // Vérification de l'organisateur
        if ($sortie->getOrganisator() === $user) {
            return 'En tant qu\'organisateur, vous ne pouvez pas vous inscrire à votre propre sortie.';
        }

        if ($now > $sortie->getRegistrationDeadline()) {
            return 'La date limite d\'inscription est dépassée.';
        }

        if ($sortie->getUsers()->contains($user)) {
            return 'Vous êtes déjà inscrit à cette sortie.';
        }

        if ($sortie->getStatus()->getStatusLabel() !== 'Ouverte') {
            return 'Cette sortie n\'est pas ouverte aux inscriptions.';
        }

        if (count($sortie->getUsers()) >= $sortie->getMaxRegistrations()) {
            return 'La sortie est complète.';
        }

        return null;
    }

    #[Route('/{id}/unregister', name: '_unregister')]
    public function unregister(Sortie $sortie, EntityManagerInterface $em, MailService $mailService): Response
    {
        $user = $this->getUser();
        $now = new \DateTime();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour vous désinscrire.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        if ($sortie->getStartDatetime() < $now) {
            $this->addFlash('error', 'Impossible de se désinscrire d\'une sortie qui a déjà eu lieu.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        if ($sortie->getStatus()->getStatusLabel() === 'Annulée') {
            $this->addFlash('error', 'Impossible de se désinscrire d\'une sortie qui a été annulée.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        if (!$sortie->getUsers()->contains($user)) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cette sortie.');
        } else {
            $sortie->removeUser($user);
            $em->flush();
            $mailService->sendUnregisterMail($user, $sortie);
            $this->addFlash('success', 'Vous avez été désinscrit de la sortie.');
        }

        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/cancel', name: '_cancel')]
    public function cancel(Sortie $sortie, Request $request, EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        if ($sortie->getOrganisator() !== $this->getUser()) {
            throw new AccessDeniedException('Vous n\'êtes pas l\'organisateur de cette sortie.');
        }

        if ($sortie->getStartDatetime() < new \DateTime()) {
            $this->addFlash('error', 'Impossible d\'annuler une sortie qui a déjà commencé.');
            return $this->redirectToRoute('_detail', ['id' => $sortie->getId()]);
        }

        $form = $this->createForm(SortieCancellationType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $canceledStatus = $statusRepository->findOneBy(['status_label' => 'Annulée']);

            if (!$canceledStatus) {
                $this->addFlash('error', 'Le statut "Annulée" n\'a pas été trouvé dans la base de données.');
                return $this->redirectToRoute('_detail', ['id' => $sortie->getId()]);
            }

            $sortie->setStatus($canceledStatus);

            $em->flush();

            $this->addFlash('success', 'La sortie a été annulée avec succès.');

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);

    }

    #[Route('/archive', name: '_archive', methods: ['GET'])]
    public function archiveSorties(SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $dateLimit = new \DateTime('-1 month');

        $sortiesToArchive = $sortieRepository->findOldSortiesForArchiving($dateLimit);

        $archivedStatus = $entityManager->getRepository(Status::class)->findOneBy(['status_label' => 'Archivée']);

        if (!$archivedStatus) {
            return new Response('Statut "Archivée" non trouvé. Impossible d\'archiver.', Response::HTTP_NOT_FOUND);
        }

        foreach ($sortiesToArchive as $sortie) {
            $sortie->setStatus($archivedStatus);
        }

        $entityManager->flush();

        return new Response(sprintf('Archivé %d sorties.', count($sortiesToArchive)));
    }

}
