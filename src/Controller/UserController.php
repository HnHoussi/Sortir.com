<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/profil')]
final class UserController extends AbstractController
{
    // List all users
    #[Route('/', name: '_list')]
    public function _list(UserRepository $userRepository): Response
    {

        $currentUser = $this->getUser();

        if (!($currentUser instanceof User)) {
            throw new AccessDeniedHttpException('Vous devez être connecté avec un compte valide.');
        }
        $currentUserId = $currentUser->getId();
        $users = $userRepository->findAllExcept($currentUserId);

        return $this->render('/user/list.html.twig', [
            'users' => $users,
        ]);
    }

    // Details of a chosen user
    #[Route('/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    public function detailUser(int $id, UserRepository $userRepository): Response
    {

        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User non trouvé');
        }
        return $this->render('/user/detail.html.twig', [
            'user' => $user,
        ]);
    }

    //Add user manually by admin
    #[Route('/admin/add', name: '_add')]
    public function addUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => true,
            'is_admin' => true,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $roles = $form->get('roles')->getData() ?? 'ROLE_USER';
            $user->setRoles([$roles]);

            //password
            $plain = $form->get('plainPassword')->getData();
            $hashed = $passwordHasher->hashPassword($user, $plain);
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur ajouté avec succès');
            return $this->redirectToRoute('_list');
        }

        return $this->render('/user/add-user.html.twig', [
            'form' => $form
        ]);
    }

    // edit existing user
    #[Route('/admin/user/{id}/edit', name: '_admin_edit', requirements: ['id' => '\d+'])]
    public function adminEditUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) { throw $this->createNotFoundException('User non trouvé'); }

        // Block editing another admin
        $currentUser = $this->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles(), true) && $user !== $currentUser) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier le profil d\'un autre administrateur.');
            return $this->redirectToRoute('_list');
        }

        $edit_profil_form = $this->createForm(UserType::class, $user, [
            'is_admin' => true,
            'require_password' => false, // édition : mot de passe optionnel
        ]);

        $edit_profil_form->handleRequest($request);
        if ($edit_profil_form->isSubmitted() && $edit_profil_form->isValid()) {
            // roles
            $roles = $edit_profil_form->get('roles')->getData() ?: ['ROLE_USER'];
            $user->setRoles($roles);

            // si admin a renseigné un nouveau mot de passe → le hasher
            $plain = $edit_profil_form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($passwordHasher->hashPassword($user, $plain));
            }

            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');
            return $this->redirectToRoute('_list');
        }

        return $this->render('user_profile/edit-profile.html.twig', [
            'edit_profil_form' => $edit_profil_form,
            'user' => $user,
        ]);
    }

}
