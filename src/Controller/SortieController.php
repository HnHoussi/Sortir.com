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

            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/create.html.twig', [
            'sortie_form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: '_detail')]
    public function detail(Sortie $sortie): Response
    {
        $isOrganisator = $this->getUser() === $sortie->getOrganisator();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'is_organisator' => $isOrganisator,
        ]);
    }

    #[Route('/{id}/cancel', name: '_cancel')]
    public function cancel(Sortie $sortie, Request $request, EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        if ($sortie->getOrganisator() !== $this->getUser()) {
            throw new AccessDeniedException('Vous n\'êtes pas l\'organisateur de cette sortie.');
        }

        if ($sortie->getStartDatetime() < new DateTime()) {
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

    #[Route('/archive', name: '_archive', methods: ['GET'])]
    public function archiveSorties(SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $dateLimit = new DateTime('-1 month');

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
