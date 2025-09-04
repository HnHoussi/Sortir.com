<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Status;
use App\Entity\User;
use App\Form\SortieCancellationType;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Repository\CityRepository;
use App\Repository\SortieRepository;
use App\Repository\StatusRepository;
use App\Service\FileUploader;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateTime;

#[Route('/sortie', name: 'sortie')]
#[IsGranted('ROLE_USER')]
final class SortieController extends AbstractController
{
    #[Route('', name: '_list')]
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {
        // Récupère l'utilisateur connecté, ou null si non connecté
        $user = $this->getUser();

        if ($user) {
            // Si l'utilisateur est connecté, on procède avec les filtres
            $form = $this->createForm(SortieFilterType::class);
            $form->handleRequest($request);

            $filters = $form->getData() ?? [];
            $sorties = $sortieRepository->findFilteredFromForm($filters, $user);
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
        }

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'form' => $form,
        ]);
    }

    #[Route('/archive', name: '_archive', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function archiveSorties(SortieRepository $sortieRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        $dateLimit = new \DateTime('-1 month');

        $sortiesToArchive = $sortieRepository->findOldSortiesForArchiving($dateLimit);
        $count = count($sortiesToArchive);

        $archivedStatus = $entityManager->getRepository(Status::class)->findOneBy(['status_label' => 'Archivée']);

        if (!$archivedStatus) {
            $this->addFlash('danger', 'Statut "Archivée" non trouvé. Impossible d\'archiver.');
        } elseif ($count > 0) {
            foreach ($sortiesToArchive as $sortie) {
                $sortie->setStatus($archivedStatus);
            }
            $entityManager->flush();
            $this->addFlash('success', sprintf('Archivé : %d sortie(s).', $count));
        } else {
            $this->addFlash('info', 'Aucune sortie de plus d\'un mois n\'a été trouvée ayant le statut "terminée" ou "annulée".');
        }

        return $this->redirectToRoute('sortie_list');
    }


    #[Route('/create', name: '_create')]
    public function create(
        EntityManagerInterface $em,
        Request                $request,
        StatusRepository       $statusRepository,
        CityRepository         $cityRepository,
        FileUploader           $fileUploader,
        ParameterBagInterface  $parameterBag
    ): Response
    {
        $user = $this->getUser();

        // Création de l'entité Sortie et assignation de l'utilisateur
        $sortie = new Sortie();
        $sortie->setOrganizer($user);
        $sortie->setCampus($user->getCampus());

        // Le statut initial est 'Créée'
        $status = $statusRepository->findOneBy(['status_label' => 'Créée']);
        $sortie->setStatus($status);

        $cities = $cityRepository->findAll();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion du fichier photo
            $file = $form->get('photoUrl')->getData();
            if ($file instanceof UploadedFile) {
                $dir = $parameterBag->get('sortie')['picture_directory'];
                $filename = $fileUploader->upload($file, $sortie->getName(), $dir);
                $sortie->setPhotoUrl($filename);
            }

            // Gestion des boutons "Enregistrer" et "Publier"
            if ($request->request->has('save')) {
                $status = $statusRepository->findOneBy(['status_label' => 'Créée']);
                $sortie->setStatus($status);
                $em->persist($sortie);
                $em->flush();
                $this->addFlash('success', 'Sortie sauvegardée en tant que brouillon !');
            } elseif ($request->request->has('publish')) {
                $status = $statusRepository->findOneBy(['status_label' => 'Ouverte']);
                $sortie->setStatus($status);

                $em->persist($sortie);
                $em->flush();
                $this->addFlash('success', 'Sortie publiée avec succès !');
            }

            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/create.html.twig', [
            'sortie_form' => $form,
            'cities' => $cities,
        ]);
    }

    #[Route('/{id}/edit', name: '_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Sortie                 $sortie,
        Request                $request,
        EntityManagerInterface $em,
        StatusRepository       $statusRepository
    ): Response
    {
        // Règle métier : seule la sortie avec le statut 'Créée' peut être modifiée
        if ($sortie->getOrganizer() !== $this->getUser() || $sortie->getStatus()->getStatusLabel() !== 'Créée') {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette sortie.');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gère les boutons "Enregistrer" et "Publier"
            if ($request->request->has('save')) {
                $em->flush();
                $this->addFlash('success', 'Sortie modifiée avec succès !');
            } elseif ($request->request->has('publish')) {
                $status = $statusRepository->findOneBy(['status_label' => 'Ouverte']);
                $sortie->setStatus($status);

                $em->flush();
                $this->addFlash('success', 'Sortie modifiée et publiée avec succès !');
            }

            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie_form' => $form,
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}', name: '_detail')]
    public function detail(Sortie $sortie): Response
    {

        $isOrganizer = $this->getUser() === $sortie->getOrganizer();
        $user = $this->getUser();
        $isOrganizer = $user && $user->getId() === $sortie->getOrganizer()->getId();
        $isRegistered = $user && $sortie->getUsers()->contains($user);
        $isFull = count($sortie->getUsers()) >= $sortie->getMaxRegistrations();
        $isRegistrationOpen = $sortie->getStatus()->getStatusLabel() === 'Ouverte' && new \DateTime() < $sortie->getRegistrationDeadline();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'is_organizer' => $isOrganizer,
            'is_registered' => $isRegistered,
            'is_full' => $isFull,
            'is_registration_open' => $isRegistrationOpen,
        ]);
    }

    #[Route('/{id}/publish', name: '_publish')]
    #[IsGranted('ROLE_USER')]
    public function publish(
        Sortie                 $sortie,
        EntityManagerInterface $em,
        StatusRepository       $statusRepository
    ): Response
    {
        if ($sortie->getOrganizer() !== $this->getUser() || $sortie->getStatus()->getStatusLabel() !== 'Créée') {
            throw $this->createAccessDeniedException('Vous ne pouvez pas publier cette sortie.');
        }

        $status = $statusRepository->findOneBy(['status_label' => 'Ouverte']);
        $sortie->setStatus($status);

        $em->flush();

        $this->addFlash('success', 'La sortie a été publiée avec succès !');

        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
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
        if ($sortie->getOrganizer() === $user) {
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
        if ($sortie->getOrganizer() !== $this->getUser()) {
            throw new AccessDeniedException('Vous n\'êtes pas l\'organisateur de cette sortie.');
        }

        if ($sortie->getStartDatetime() < new \DateTime()) {
            $this->addFlash('error', 'Impossible d\'annuler une sortie qui a déjà commencé.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $form = $this->createForm(SortieCancellationType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $canceledStatus = $statusRepository->findOneBy(['status_label' => 'Annulée']);

            if (!$canceledStatus) {
                $this->addFlash('error', 'Le statut "Annulée" n\'a pas été trouvé dans la base de données.');
                return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
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


    #[Route('/sortie/{id}/annuler', name: '_annuler')]
    #[IsGranted('ROLE_ADMIN')]
    public function annulerSortie(Request $request, Sortie $sortie, EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        $now = new \DateTime();

        if ($sortie->getStartDatetime() <= $now) {
            $this->addFlash('error', 'Impossible d\'annuler : la sortie a déjà commencé.');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getOrganizer() === $this->getUser()) {
            $this->addFlash('warning', 'Utilisez l’annulation standard en tant qu’organisateur.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motif');
            $etatAnnulee = $statusRepository->findOneBy(['status_label' => 'Annulée']);

            if (!$etatAnnulee) {
                $this->addFlash('error', 'Statut "Annulée" introuvable.');
                return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
            }

            $sortie->setStatus($etatAnnulee);
            $sortie->setMotifAnnulation($motif);
            $em->flush();

            $this->addFlash('success', 'Sortie annulée avec succès.');
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/annuler_sortie.html.twig', [
            'sortie' => $sortie,
        ]);
    }


    #[Route('/{id}/delete', name: 'sortie_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request,Sortie $sortie, EntityManagerInterface $em): Response
    {
        $em->remove($sortie);
        $em->flush();

        $this->addFlash('success', 'Sortie supprimée avec succès.');
        return $this->redirectToRoute('sortie_list');
    }

}
