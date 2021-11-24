<?php

namespace App\Form;

use PhpParser\Node\Expr\BinaryOp\Greater;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\GreaterThan;

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
                'required' => true
            ])
            ->add('dateDepart', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,

            ])
            ->add('typeVehicule', ChoiceType::class, [
                'choices'  => [
                    'Classic' => 'Classic',
                ],

                'required' => true
            ])
            ->add('agenceRetour', ChoiceType::class, [
                'choices'  => [
                    'AEROPORT DE POINT-A-PITRE' => 'AEROPORT DE POINT-A-PITRE',
                    'AGENCE DU MOULE' => 'AGENCE DU MOULE',
                    'GARE MARITIME DE BERGERVIN' => 'GARE MARITIME DE BERGERVIN',
                ],
                'required' => true
            ])
            ->add('dateRetour', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new GreaterThan("+2 hours UTC+3")
                ]
            ])
            ->add('lieuSejour', TextType::class, [
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
