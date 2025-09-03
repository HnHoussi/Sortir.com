<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
final class UserProfileController extends AbstractController
{
    #[Route('/my-profile', name: 'user_profile_details')]
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

    #[Route('/my-profile/edit', name: 'user_profile_edit')]
    public function editProfile(
        SluggerInterface $slugger,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, $user, [
            'is_admin' => false,
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $passwordValid = true;

            // Handle password change
            $newPassword = $form->get('newPassword')->getData();
            $oldPassword = $form->get('oldPassword')->getData();
            if ($newPassword) {
                if (!$oldPassword || !$passwordHasher->isPasswordValid($user, $oldPassword)) {
                    $form->get('oldPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));
                    $passwordValid = false;
                } else {
                    $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                }
            }

            // Handle avatar upload
            $avatarFile = $form->get('avatarFilename')->getData();
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move($this->getParameter('avatars_directory'), $newFilename);
                    $user->setAvatarFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Impossible de télécharger le fichier.');
                }
            }

            // Only flush if form is valid and password is valid
            if ($form->isValid() && $passwordValid) {
                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès !');

                return $this->redirectToRoute('user_profile_details');
            }
        }

        return $this->render('user_profile/edit-profile.html.twig', [
            'edit_profil_form' => $form->createView(),
            'editedUser' => $user,
        ]);
    }
}
