<?php

namespace App\Form;

use App\Entity\Place;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez le nom de la sortie',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('startDatetime', DateTimeType::class, [
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
            ->add('registrationDeadline', DateTimeType::class, [
                'label' => "Date limite d'inscription",
                'html5' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('maxRegistrations', NumberType::class, [
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
            ->add('photoUrl', FileType::class, [
                'label' => 'Image de la sortie',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Lien vers une image (optionnel)',
                    'class' => 'form-control rounded-lg',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024K', // Taille maximale du fichier
                        'maxSizeMessage' => 'Le fichier ne doit pas dépasser 1 Mo',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Format de fichier non valide, veuillez télécharger une image au format JPEG, JPG ou PNG.',
                    ])
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
//SLB : remplacé par enregistrer et publier qui sont dans create twig
//            ->add('submit', SubmitType::class, [
//                'label' => 'Créer la sortie',
//                'attr' => ['class' => 'btn btn-success mt-3 mb-3 rounded-lg w-100']
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
