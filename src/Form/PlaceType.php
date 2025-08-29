<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('place_name', TextType::class, [
                'label' => 'Nom du lieu',
                'attr' => [
                    'placeholder' => 'Entrez le nom du lieu',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez la rue (optionnel)',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: 47.218371',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: -1.553621',
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'cityName',
                'label' => 'Ville',
                'placeholder' => 'Sélectionnez une ville',
                'attr' => [
                    'class' => 'form-control rounded-lg'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer le lieu',
                'attr' => ['class' => 'btn btn-success mt-3 mb-3 rounded-lg w-100']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
