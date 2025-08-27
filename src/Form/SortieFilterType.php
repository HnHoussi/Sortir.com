<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\City;
use App\Entity\Campus;
use App\Entity\Status;

class SortieFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => false,
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'city_name',
                'placeholder' => 'Toutes les villes',
                'required' => false,
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'campus_name',
                'placeholder' => 'Tous les campus',
                'required' => false,
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'status_label',
                'placeholder' => 'Tous les états',
                'required' => false,
            ])
            ->add('organizer', CheckboxType::class, [
                'label' => 'Je suis l’organisateur',
                'required' => false,
            ])
            ->add('participant', CheckboxType::class, [
                'label' => 'Je suis inscrit',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
