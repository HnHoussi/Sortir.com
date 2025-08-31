<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('startDatetime')
            ->add('duration')
            ->add('registration_deadline')
            ->add('max_registrations')
            ->add('description')
            ->add('photo_url')
            ->add('organizer')
            ->add('place')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Créée'      => 'Créée',
                    'Ouverte'    => 'Ouverte',
                    'Fermée'     => 'Fermée',
                    'En cours'   => 'En cours',
                    'Terminée'   => 'Terminée',
                    'Annulée'    => 'Annulée',
                    'Archivée'   => 'Archivée',
                ],
                'label' => 'Statut',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
