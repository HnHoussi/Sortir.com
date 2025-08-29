<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[IsGranted('ROLE_USER')]
final class UserProfileController extends AbstractController
{
    #[Route('/mon-profile', name: 'app_user_profile')]
    public function showConnectedUserProfile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user_profile/user-profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/mon-profile/edit', name: 'app_user_profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $edit_profil_form = $this->createForm(UserType::class, $user, [
            'is_admin' => false,
        ]);
        $edit_profil_form->handleRequest($request);

        if ($edit_profil_form->isSubmitted() && $edit_profil_form->isValid()) {

            $newPassword = $edit_profil_form->get('newPassword')->getData();

            // Only check old password if a new password is provided
            if ($newPassword) {
                $oldPassword = $edit_profil_form->get('oldPassword')->getData();

                if (!$oldPassword || !$passwordHasher->isPasswordValid($user, $oldPassword)) {
                    $edit_profil_form->get('oldPassword')->addError(
                        new FormError('Le mot de passe actuel est incorrect.')
                    );
                } else {
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                }
            }

            // Save the other user info regardless of password
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user_profile/edit-profile.html.twig', [
            'edit_profil_form' => $edit_profil_form,
            'user' => $user,
        ]);
    }
}
