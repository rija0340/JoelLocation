<?php

namespace App\Form;

use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ReservationStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('agenceDepart', ChoiceType::class, [
                'choices'  => [
                    'AEROPORT DE POINT-A-PITRE' => 'AEROPORT DE POINT-A-PITRE',
                    'AGENCE DU MOULE' => 'AGENCE DU MOULE',
                    'GARE MARITIME DE BERGERVIN' => 'GARE MARITIME DE BERGERVIN',
                ],
            ])
            ->add('dateDepart', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('typeVehicule', ChoiceType::class, [
                'choices'  => [
                    'Classic' => 'Classic',
                ],
            ])
            ->add('agenceRetour', ChoiceType::class, [
                'choices'  => [
                    'AEROPORT DE POINT-A-PITRE' => 'AEROPORT DE POINT-A-PITRE',
                    'AGENCE DU MOULE' => 'AGENCE DU MOULE',
                    'GARE MARITIME DE BERGERVIN' => 'GARE MARITIME DE BERGERVIN',
                ],
            ])
            ->add('dateRetour', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('lieuSejour');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
