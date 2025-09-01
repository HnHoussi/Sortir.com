<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('lastName', null, ['label' => 'Nom'])
            ->add('pseudo', null, ['label' => 'Pseudo'])
            ->add('phone', null, ['label' => 'Téléphone', 'required' => false])
            ->add('email', null, ['label' => 'Adresse e-mail'])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'campusName',
                'label' => 'Campus',
                'placeholder' => '----Choisissez un campus----'
            ])
            ->add('avatarFilename', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'help' => 'Choisissez un fichier image (JPEG, PNG ou WEBP, max 2 Mo)',
                'attr' => [
                    'class' => 'form-control',
                    'title' => 'Choisissez une image',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg','image/png','image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou WEBP)',
                    ])
                ],
            ]);

        if ($options['is_admin']) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => [
                    'Invité' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label' => 'Rôle',
                'data' => 'ROLE_USER',
            ]);

            if (!$options['is_edit']) {
                // Creating a new user → password required
                $builder->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'mapped' => false,
                    'required' => true,
                    'first_options' => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmer le mot de passe'],
                    'invalid_message' => 'Les mots de passe doivent correspondre.',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                        new Assert\Length(['min' => 8, 'max' => 4096]),
                        new Assert\Regex([
                            'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/',
                            'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une minuscule et un chiffre.',
                        ]),
                    ],
                ]);
            }
        } else {
            // Editing own profile → password change optional
            if ($options['is_edit']) {
                $builder
                    ->add('oldPassword', PasswordType::class, [
                        'mapped' => false,
                        'required' => false,
                        'label' => 'Mot de passe actuel',
                    ])
                    ->add('newPassword', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'mapped' => false,
                        'required' => false,
                        'first_options' => ['label' => 'Nouveau mot de passe'],
                        'second_options' => ['label' => 'Confirmer le nouveau mot de passe'],
                        'invalid_message' => 'Les mots de passe doivent correspondre.',
                        'constraints' => [
                            new Assert\Length(['min' => 8, 'max' => 4096]),
                            new Assert\Regex([
                                'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/',
                                'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une minuscule et un chiffre.',
                            ]),
                        ],
                    ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_admin' => false,
            'is_edit' => false,
        ]);
    }
}
