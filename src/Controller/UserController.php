<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/profil')]
final class UserController extends AbstractController
{
    #[Route('/', name: '_list')]
    public function _list(UserRepository $userRepository): Response
    {
        //methode pour afficher la liste de tous les users

        $users = $userRepository->findAll();

        return $this->render('/user/list.html.twig', [
            'users' => $users,
        ]);
    }


    #[Route('/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, UserRepository $userRepository): Response
    {
        //methode pour afficher le detail d'un user
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User non trouvé');
        }
        return $this->render('/user/detail.html.twig', [
            'user' => $user,
        ]);
    }
}
