<?php

namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cityName', TextType::class, [
                'label' => 'Nom de la ville',
                'attr' => ['placeholder' => 'Ex: Paris']
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code Postal',
                'attr' => ['placeholder' => 'Ex: 75001']
            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 48.8566']
            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 2.3522']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
