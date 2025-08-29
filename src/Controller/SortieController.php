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
#[Route('/sortie', name: 'sortie')]
final class SortieController extends AbstractController
{
//    SLB : modif de la fonction pour que l'affichage par défaut, sans utiliser de filtre, exclue les sorties archivées de la vue
    #[Route('', name: '_list')]
    public function list(SortieRepository $sortieRepository, Request $request, User $user): Response
    {
        $form = $this->createForm(SortieFilterType::class);
        $form->handleRequest($request);

        $filters = $form->getData();
        $sorties = $sortieRepository->findFilteredFromForm($filters, $user);

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'form' => $form,
        ]);
    }

    #[Route('/create', name: '_create')]
    public function create(EntityManagerInterface $em, Request $request): Response
    {
        // Logique pour créer une sortie
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie = $form->getData();

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

        $isOrganisator = $this->getUser() === $sortie->getOrganisator();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'is_organisator' => $isOrganisator,
        ]);
    }

    #[Route('/{id}/cancel', name: '_cancel')]
    public function cancel (Sortie $sortie, Request $request, EntityManagerInterface $em, StatusRepository $statusRepository): Response
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

    #[Route('/archive', name: 'sortie_archive', methods: ['GET'])]
    public function archiveSorties(SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $dateLimit = new \DateTime('-1 month');

        $sortiesToArchive = $sortieRepository->findOldSortiesForArchiving($dateLimit);

        $archivedStatus = $entityManager->getRepository(Status::class)->findOneBy(['code' => 'Archivée']);

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
