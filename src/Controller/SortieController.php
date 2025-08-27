<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieCancellationType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortie')]
final class SortieController extends AbstractController
{
    #[Route('/', name: '_list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    #[Route('/create', name: '_create')]
    public function create(EntityManagerInterface $em, Request $request): Response
    {
        // Logique pour créer un sortie
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
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie inexistante');
        }

        $isOrganizer = $this->getUser() === $sortie->getOrganisateur();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'is_organizer' => $isOrganizer,
        ]);
    }

    #[Route('/{id}/cancel', name: '_cancel')]
    public function cancel (Sortie $sortie, Request $request, EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        if ($sortie->getOrganisateur() !== $this->getUser()) {
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

            return $this->redirectToRoute('_list');
        }

        return $this->render('sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);

    }

}
