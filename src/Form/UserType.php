<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, ['label' => 'Prénom',])
            ->add('lastName', null, ['label' => 'Nom',])
            ->add('pseudo', null, ['label' => 'Pseudo',])
            ->add('phone', null, ['label' => 'Téléphone',])
            ->add('email', null, ['label' => 'Adresse e-mail',])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'campusName',
                'label' => 'Campus',
            ]);

        if($options['is_admin']) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'choices' => [
                        'Invité' => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN',
                    ],
                    'multiple' => false,
                    'expanded' => true,
                    'mapped' => false,
                    'label' => 'Rôle',
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'mapped' => false,
                    'required' => $options['require_password'], // true for create, false for edit
                    'first_options' => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmer le mot de passe'],
                    'invalid_message' => 'Les mots de passe doivent correspondre.',
                ]);
        }else {
            $builder
            // old password
            ->add('oldPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Mot de passe actuel',
            ])
            // new password and confirmation
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmer le nouveau mot de passe'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_admin' => false,
            'require_password' => false,
        ]);
    }
}
