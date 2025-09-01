<?php

namespace App\Form;

use App\Entity\Place;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => [
                    'placeholder' => 'Entrez le nom de la sortie',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('publicationDate', DateTimeType::class, [
                'label' => 'Date de publication',
                'html5' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control rounded-lg'
                ],
                'required' => false,
            ])
            ->add('start_datetime', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'html5' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('duration', NumberType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => [
                    'placeholder' => 'ex: 60 pour 1h',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('registration_deadline', DateTimeType::class, [
                'label' => "Date limite d'inscription",
                'html5' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('max_registrations', NumberType::class, [
                'label' => "Nombre maximum d'inscriptions",
                'attr' => [
                    'placeholder' => 'ex: 10',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la sortie',
                'attr' => [
                    'placeholder' => 'Détails, instructions, etc.',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('photo_url', TextType::class, [
                'label' => 'URL de la photo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Lien vers une image (optionnel)',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'placeName',
                'required' => false,
                'placeholder' => 'Sélectionnez un lieu',
                'label' => 'Lieu',
                'attr' => [
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer la sortie',
                'attr' => ['class' => 'btn btn-success mt-3 mb-3 rounded-lg w-100']
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
