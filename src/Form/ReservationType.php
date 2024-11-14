<?php

namespace App\Form;

use App\Entity\Vehicule;
use App\Entity\Reservation;
use App\Form\FraisSupplResaType;
use Symfony\Component\Form\AbstractType;
use App\Repository\ReservationRepository;
use DateTime;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $reservation = $options['data']; // Access the Reservation entity
        // Compute the value for the unmapped field
        $prixOptionsGaranties = $prixOptionsGaranties = $reservation  ?
        $reservation->getPrixOptions() + $reservation->getPrixGaranties()
        : 0;

        $builder
            ->add('date_debut', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => [
                    "step" => "any"
                ]
            ])
            ->add('date_fin', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => [
                    "step" => "any"
                ]
            ])
            ->add('tarifVehicule', NumberType::class)
            ->add('prix', NumberType::class,
            [
                'attr' => [
                    'readonly' => true, // Optional: Make it readonly
                ],
            ])
            ->add('prixOptionsGaranties', NumberType::class, [
                'mapped' => false, // Not linked to the Reservation entity
                'data' => $prixOptionsGaranties, // Set the computed value
                'required' => false,
                'attr' => [
                    'readonly' => true, // Optional: Make it readonly
                ],
                ])
                ->add('immatriculation', HiddenType::class, [
                    'mapped' => false,
                    'data' => $reservation->getVehicule()->getImmatriculation(), // Set the computed value
                    'required' => false
                ])

            // ->add('type')
            // ->add('date_reservation', DateTimeType::class, [
            //     'widget' => 'single_text',
            // ])
            // ->add('lieu')
            // ->add('code_reservation')
            // ->add('client')
            // ->add('mode_reservation')
            // ->add('prix')
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
            ->add('vehicule', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
