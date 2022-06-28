<?php

namespace App\Form;

use App\Entity\FraisSupplResa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FraisSupplResaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class)
            ->add('prixUnitaire', NumberType::class, [
                'invalid_message' => 'Ce doit être un nombre',
            ])
            ->add('quantite', NumberType::class, [
                'invalid_message' => 'Ce doit être un nombre',
            ])
            ->add('remise', NumberType::class, [
                'invalid_message' => 'Ce doit être un nombre',
            ])
            ->add('totalHT', NumberType::class, [
                'invalid_message' => 'Ce doit être un nombre',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FraisSupplResa::class,
        ]);
    }
}
