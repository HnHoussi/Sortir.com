<?php

namespace App\Form;

use App\Repository\StatusRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('ville', EntityType::class, [
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
//            SLB : modif du filtre pour ne pas afficher les sorties archivées
            ->add('statut', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'status_label',
                'placeholder' => 'Tous les états',
                'required' => false,
                'query_builder' => function (StatusRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->andWhere('s.status_label != :archived_status')
                        ->setParameter('archived_status', 'Archivée');
                }
            ])
            ->add('organisateur', CheckboxType::class, [
                'label' => 'Je suis l’organisateur',
                'required' => false,
            ])
            ->add('user', CheckboxType::class, [
                'label' => 'Je suis inscrit',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
