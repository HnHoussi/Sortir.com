<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\AnonymizerService;
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
    #[Route('/', name: 'admin_users_list')]
    public function showUsersList(UserRepository $userRepository): Response
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
    #[Route('user/{id}', name: 'user_detail', requirements: ['id' => '\d+'])]
    public function detailUser(int $id, UserRepository $userRepository): Response
    {

        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        return $this->render('/user/detail.html.twig', [
            'user' => $user,
        ]);
    }

    //Add user manually by admin
    #[Route('/admin/user/add', name: 'admin_user_add')]
    public function adminAddUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => false,
            'is_admin' => true,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $roles = $form->get('roles')->getData();
            $user->setRoles([$roles]);

            //password
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur ajouté avec succès');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('/user/add-user.html.twig', [
            'form' => $form
        ]);
    }

    // edit existing user
    #[Route('/admin/user/{id}/edit', name: 'admin_edit_user', requirements: ['id' => '\d+'])]
    public function adminEditUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User non trouvé');
        }

        // Block editing another admin (unless it’s yourself)
        $currentUser = $this->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles(), true) && $user !== $currentUser) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier le profil d\'un autre administrateur.');
            return $this->redirectToRoute('admin_users_list');
        }

        $edit_profil_form = $this->createForm(UserType::class, $user, [
            'is_admin' => true,
            'is_edit' => true, // editing existing user → no password field
        ]);

        $edit_profil_form->handleRequest($request);

        if ($edit_profil_form->isSubmitted() && $edit_profil_form->isValid()) {
            // roles
            if ($edit_profil_form->has('roles')) {
                $roles = $edit_profil_form->get('roles')->getData();
                $user->setRoles([$roles]);
            }

            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('user_profile/edit-profile.html.twig', [
            'edit_profil_form' => $edit_profil_form,
            'editedUser' => $user,
        ]);
    }


    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete')]
    public function adminDeleteUser(User $user, EntityManagerInterface $em, AnonymizerService $anonymizer): Response
    {
        $currentUser = $this->getUser();

        // Prevent deleting another admin
        if (in_array('ROLE_ADMIN', $user->getRoles(), true) && $user !== $currentUser) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer un autre administrateur.');
            return $this->redirectToRoute('admin_users_list');
        }

        $anonymizer->anonymize($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé et anonymisé avec succès.');
        return $this->redirectToRoute('admin_users_list');
    }

}
