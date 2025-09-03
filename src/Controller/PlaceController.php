<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\CityRepository;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/place')]
final class PlaceController extends AbstractController
{
    #[Route(name: 'app_place_index', methods: ['GET'])]
    public function index(PlaceRepository $placeRepository): Response
    {
        return $this->render('place/index.html.twig', [
            'places' => $placeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_place_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $place = new Place();
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($place);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_create', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('place/new.html.twig', [
            'place' => $place,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_place_show', methods: ['GET'])]
    public function show(Place $place): Response
    {
        return $this->render('place/show.html.twig', [
            'place' => $place,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_place_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Place $place, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_place_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('place/edit.html.twig', [
            'place' => $place,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_place_delete', methods: ['POST'])]
    public function delete(Request $request, Place $place, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$place->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($place);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_place_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/ajax/create', name: 'place_create_ajax', methods: ['POST'])]
    public function createAjax(Request $request, EntityManagerInterface $em, CityRepository $cityRepo): JsonResponse
    {
        $cityId = $request->request->get('city');
        $city = $cityRepo->find($cityId);

        if (!$city) {
            return new JsonResponse(['success' => false, 'error' => 'Ville introuvable']);
        }

        $place = new Place();
        $place->setPlaceName($request->request->get('name'));
        $place->setStreet($request->request->get('address'));
        $place->setCity($city);

        $em->persist($place);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'id' => $place->getId(),
            'name' => $place->getPlaceName()
        ]);
    }

}
