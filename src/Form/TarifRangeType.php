<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class TarifRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min_days', NumberType::class, [
                'label' => 'Jours minimum',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control-range-min',
                ],
            ])
            ->add('max_days', NumberType::class, [
                'label' => 'Jours maximum',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control-range-max',
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2,
                'attr' => [
                    'min' => 0,
                    'step' => '0.01',
                    'class' => 'form-control-range-price',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'tarif_range';
    }
}
