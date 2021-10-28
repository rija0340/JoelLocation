<?php

namespace App\Form;

use App\Entity\InfosVolResa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class InfosVolResaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('compagnieAller', ChoiceType::class, [
                'choices'  => [
                    'Air Antilles Express' => 'Air Antilles Express',
                    'Air Belgium' => 'Air Belgium',
                    'Air Canada' => 'Air Canada',
                ],
            ])
            ->add('compagnieRetour', ChoiceType::class, [
                'choices'  => [
                    'Air Antilles Express' => 'Air Antilles Express',
                    'Air Belgium' => 'Air Belgium',
                    'Air Canada' => 'Air Canada',
                ],
            ])
            ->add('numVolAller')
            ->add('numVolRetour')
            ->add('heureVolAller', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('heureVolRetour', DateTimeType::class, [
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InfosVolResa::class,
        ]);
    }
}
