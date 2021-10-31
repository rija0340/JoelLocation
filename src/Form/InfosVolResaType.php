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
                    'Air Caraîbes' => 'Air Caraîbes',
                    'Air France' => 'Air France',
                    'Air Transat' => 'Air Transat',
                    'American Airlines' => 'American Airlines',
                    'Autres' => 'Autres',
                    'CorsAir' => 'CorsAir',
                    'Jet Blue' => 'Jet Blue',
                    'Level' => 'Level',
                    'Norvegian' => 'Norvegian',
                    'XL Airways' => 'XL Airways',
                ],
            ])
            ->add('compagnieRetour', ChoiceType::class, [
                'choices'  => [
                    'Air Antilles Express' => 'Air Antilles Express',
                    'Air Belgium' => 'Air Belgium',
                    'Air Canada' => 'Air Canada',
                    'Air Caraîbes' => 'Air Caraîbes',
                    'Air France' => 'Air France',
                    'Air Transat' => 'Air Transat',
                    'American Airlines' => 'American Airlines',
                    'Autres' => 'Autres',
                    'CorsAir' => 'CorsAir',
                    'Jet Blue' => 'Jet Blue',
                    'Level' => 'Level',
                    'Norvegian' => 'Norvegian',
                    'XL Airways' => 'XL Airways',
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
