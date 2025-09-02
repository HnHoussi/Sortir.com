<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\AnonymizerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function adminAddUserManually(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => false,
            'is_admin' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Set role
            $roles = $form->get('roles')->getData();
            $user->setRoles([$roles]);

            // Set default password
            $defaultPassword = 'Password 1'; // <-- default password
            $hashedPassword = $passwordHasher->hashPassword($user, $defaultPassword);
            $user->setPassword($hashedPassword);

            // Avatar upload
            $avatarFile = $form->get('avatarFilename')->getData();
            if ($avatarFile) {
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                    $user->setAvatarFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'avatar.');
                }
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur ajouté avec succès');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('/user/add-user.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //Add multiple users by admin via CSV
    #[Route('/admin/user/import', name: 'admin_user_import')]
    public function adminImportUsers(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createFormBuilder()
            ->add('CsvFile', FileType::class, [
                'label' => 'Fichier CSV',
                'mapped' => false,
                'required' => true,
                'attr' => ['accept' => '.csv'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('CsvFile')->getData();

            if ($csvFile) {
                // Optional: check MIME type for extra security
                if ($csvFile->getMimeType() !== 'text/plain' && $csvFile->getMimeType() !== 'text/csv') {
                    $this->addFlash('danger', 'Le fichier doit être un CSV valide.');
                    return $this->redirectToRoute('admin_user_import');
                }

                $filePath = $csvFile->getRealPath();

                if (($handle = fopen($filePath, 'r')) !== false) {
                    fgetcsv($handle); // skip header
                    $lineNumber = 1;
                    $successCount = 0;
                    $errors = [];

                    while (($data = fgetcsv($handle)) !== false) {
                        $lineNumber++;

                        if (count($data) < 5) {
                            $errors[] = "Ligne $lineNumber: données incomplètes.";
                            continue;
                        }

                        [$email, $firstName, $lastName, $phone, $role] = $data;

                        // Validate email
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Ligne $lineNumber: email '$email' invalide.";
                            continue;
                        }

                        // Validate role
                        if (!in_array($role, ['ROLE_USER', 'ROLE_ADMIN'])) {
                            $errors[] = "Ligne $lineNumber: rôle '$role' invalide.";
                            continue;
                        }

                        // Optional: phone validation (digits only)
                        if (!preg_match('/^\+?[0-9]{6,15}$/', $phone)) {
                            $errors[] = "Ligne $lineNumber: numéro de téléphone '$phone' invalide.";
                            continue;
                        }

                        // Skip existing users
                        if ($userRepository->findOneBy(['email' => $email])) {
                            $errors[] = "Ligne $lineNumber: utilisateur '$email' existe déjà.";
                            continue;
                        }

                        // Create user
                        $user = new User();
                        $user->setEmail($email);
                        $user->setFirstName($firstName);
                        $user->setLastName($lastName);
                        $user->setPhone($phone);
                        $user->setRoles([$role]);

                        // Random secure password
                        $defaultPassword = 'Password 1'; // <-- default password
                        $hashedPassword = $passwordHasher->hashPassword($user, $defaultPassword);
                        $user->setPassword($hashedPassword);

                        $em->persist($user);
                        $successCount++;
                    }

                    fclose($handle);

                    // Flush only after processing all valid users
                    $em->flush();

                    // Flash messages
                    if ($successCount > 0) {
                        $this->addFlash('success', "$successCount utilisateur(s) importé(s) avec succès.");
                    }

                    if ($errors) {
                        foreach ($errors as $error) {
                            $this->addFlash('warning', $error);
                        }
                    }

                    return $this->redirectToRoute('admin_users_list');
                } else {
                    $this->addFlash('danger', 'Impossible d’ouvrir le fichier CSV.');
                }
            }
        }

        return $this->render('/user/import-users.html.twig', [
            'form' => $form->createView(),
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

        $currentUser = $this->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles(), true) && $user !== $currentUser) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier le profil d\'un autre administrateur.');
            return $this->redirectToRoute('admin_users_list');
        }

        $edit_profil_form = $this->createForm(UserType::class, $user, [
            'is_admin' => true,
            'is_edit' => true,
        ]);

        $edit_profil_form->handleRequest($request);

        if ($edit_profil_form->isSubmitted() && $edit_profil_form->isValid()) {
            if ($edit_profil_form->has('roles')) {
                $roles = $edit_profil_form->get('roles')->getData();
                $user->setRoles([$roles]);
            }

            // Avatar upload
            $avatarFile = $edit_profil_form->get('avatarFilename')->getData();
            if ($avatarFile) {
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                    $user->setAvatarFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'avatar.');
                }
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
        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('admin_users_list');
    }

}
