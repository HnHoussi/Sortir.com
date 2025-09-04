<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CityController extends AbstractController
{
    #[Route('/city/add', name: 'app_city_add', methods: ['GET', 'POST'])]
    public function addCity(Request $request, EntityManagerInterface $entityManager): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();

            $this->addFlash('success', 'La ville a été ajoutée avec succès !');
            return $this->redirectToRoute('app_city_add');
        }

        return $this->render('city/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/cities', name: 'app_admin_cities', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function manageCities(
        Request $request,
        EntityManagerInterface $entityManager,
        CityRepository $cityRepository
    ): Response {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();

            $this->addFlash('success', 'La ville a été ajoutée avec succès !');
            return $this->redirectToRoute('app_admin_cities');
        }

        return $this->render('city/manage.html.twig', [
            'form' => $form->createView(),
            'cities' => $cityRepository->findAll(),
        ]);
    }

    #[Route('/admin/cities/edit/{id}', name: 'app_admin_city_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editCity(Request $request, City $city, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La ville a été modifiée avec succès.');
            return $this->redirectToRoute('app_admin_cities');
        }

        return $this->render('city/edit.html.twig', [
            'form' => $form->createView(),
            'city' => $city,
        ]);
    }

    #[Route('/admin/cities/delete/{id}', name: 'app_admin_city_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(City $city, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($city);
        $entityManager->flush();

        $this->addFlash('success', 'La ville a été supprimée.');
        return $this->redirectToRoute('app_admin_cities');
    }
}
