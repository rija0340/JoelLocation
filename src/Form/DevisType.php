<?php

namespace App\Form;

use App\Entity\Devis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('agenceDepart')
            ->add('agenceRetour')
            ->add('lieuSejour')
            ->add('conducteur')
            ->add('siege')
            ->add('garantie')
            ->add('Client')
            ->add('Vehicule');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
