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
                    'Aéroport de Point-à-pitre' => 'Aéroport de Point-à-pitre',
                    'Agence du Moule' => 'Agence du Moule',
                    'Gare Maritime de Bergervin' => 'Gare Maritime de Bergervin',
                    'Gare maritime de Saint-François' => 'Gare maritime de Saint-François',
                    'Point de livraison : ' =>
                    [
                        'Abymes' => 'Abymes',
                        'Anse-bertrand' => 'Anse-bertrand',
                        'Gosier' => 'Gosier',
                        'Moule' => 'Moule',
                        "Morne-à-l'Eau" => "Morne-à-l'Eau",
                        "Petit-canal" => "Petit-canal",
                        "Pointe-à-pitre" => "Pointe-à-pitre",
                        "Port-louis" => "Port-louis",
                        "Sainte-anne" => "Sainte-anne",
                        "Saint-François" => "Saint-François",
                    ]
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
                    'Aéroport de Point-à-pitre' => 'Aéroport de Point-à-pitre',
                    'Agence du Moule' => 'Agence du Moule',
                    'Gare Maritime de Bergervin' => 'Gare Maritime de Bergervin',
                    'Gare maritime de Saint-François' => 'Gare maritime de Saint-François',
                    'Point de livraison : ' =>
                    [
                        'Abymes' => 'Abymes',
                        'Anse-bertrand' => 'Anse-bertrand',
                        'Gosier' => 'Gosier',
                        'Moule' => 'Moule',
                        "Morne-à-l'Eau" => "Morne-à-l'Eau",
                        "Petit-canal" => "Petit-canal",
                        "Pointe-à-pitre" => "Pointe-à-pitre",
                        "Port-louis" => "Port-louis",
                        "Sainte-anne" => "Sainte-anne",
                        "Saint-François" => "Saint-François",
                    ]
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
