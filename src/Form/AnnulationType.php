<?php

namespace App\Form;

use App\Entity\AnnulationReservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType; // Changed from FloatType to NumberType

class AnnulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motif', TextareaType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Avec avoir' => "avec_avoir",
                    'Sans avoir' => "sans_avoir",
                ],
            ])
            ->add('montant', NumberType::class, [ // Changed from FloatType to NumberType
                'mapped' => false,
                'required' => false,
                'scale' => 2, // Optional: number of decimal places
                'attr' => [
                    'step' => '0.01', // HTML5 step attribute for float input
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnnulationReservation::class,
        ]);
    }
}