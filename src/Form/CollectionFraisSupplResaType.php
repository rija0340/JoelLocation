<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Form\FraisSupplResaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CollectionFraisSupplResaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'fraisSupplResas',
                CollectionType::class,
                [
                    'entry_type' => FraisSupplResaType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'prototype' => true,
                    'by_reference' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
