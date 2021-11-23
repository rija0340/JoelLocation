<?php

namespace App\Form;

use App\Entity\InfosVolResa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
            ->add('numVolAller', TextType::class, [
                'required' => false
            ])
            ->add('numVolRetour', TextType::class, [
                'required' => false
            ])
            ->add('heureVolAller', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('heureVolRetour', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InfosVolResa::class,
        ]);
    }
}
