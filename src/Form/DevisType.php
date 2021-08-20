<?php

namespace App\Form;

use App\Entity\Devis;
use App\Entity\Options;
use App\Entity\Garantie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDepart', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('dateRetour', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('agenceDepart', ChoiceType::class, [
                'choices'  => [
                    'AEROPORT DE POINT-A-PITRE' => 'aeroport',
                    'AGENCE DU MOULE' => 'agence',
                    'GARE MARITIME DE BERGERVIN' => 'gareMaritime',
                ],
            ])
            ->add('agenceRetour', ChoiceType::class, [
                'choices'  => [
                    'AEROPORT DE POINT-A-PITRE' => 'aeroport',
                    'AGENCE DU MOULE' => 'agence',
                    'GARE MARITIME DE BERGERVIN' => 'gareMaritime',
                ],
            ])
            ->add('lieuSejour')
            ->add('conducteur')
            ->add('options', EntityType::class, [
                'class' => Options::class,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('garanties', EntityType::class, [
                'class' => Garantie::class,
                'multiple' => true,
                'expanded' => true,
                // 'data' => true,
                'label' => 'Garanties',
                // 'choice_label' => 'id'
                // 'choice_label' => 'description',
            ])
            ->add('client')
            ->add('vehicule');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
