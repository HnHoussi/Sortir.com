<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Place;
use App\Entity\Status;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'label' => 'Ville',
                'choice_label' => 'cityName', // Assure-toi que City a bien cette propriété
                'placeholder' => 'Toutes les villes',
                'required' => false,
            ])
            ->add('lieu', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'placeName', // correspond au getter getPlaceName()
                'placeholder' => 'Tous les lieux',
                'required' => false,
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'statusLabel',
                'placeholder' => 'Tous les états',
                'required' => false,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('s')
                        ->where('s.status_label != :status_label')
                        ->setParameter('status_label', 'Créée')
                        ->orderBy('s.status_label', 'ASC');
                },
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'campusName', // idem, vérifie le getter
                'placeholder' => 'Tous les campus',
                'required' => false,
            ])
            ->add('organizer', CheckboxType::class, [
                'label' => 'Je suis l’organisateur',
                'required' => false,
            ])
            ->add('user', CheckboxType::class, [
                'label' => 'Je suis inscrit',
                'required' => false,
            ])
            ->add('not_user', CheckboxType::class, [
                'label' => 'Je ne suis pas inscrit',
                'required' => false,
            ])
            ->add('past', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas de data_class ici car c’est un formulaire de filtre
            'method' => 'GET', // utile pour les filtres
            'csrf_protection' => false,
        ]);
    }
}
