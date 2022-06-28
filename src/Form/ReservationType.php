<?php

namespace App\Form;

use App\Entity\Vehicule;
use App\Entity\Reservation;
use App\Form\FraisSupplResaType;
use Symfony\Component\Form\AbstractType;
use App\Repository\ReservationRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_debut', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('date_fin', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            // ->add('type')
            // ->add('date_reservation', DateTimeType::class, [
            //     'widget' => 'single_text',
            // ])
            // ->add('lieu')
            // ->add('code_reservation')
            // ->add('client')
            // ->add('mode_reservation')
            // ->add('etat_reservation')
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
            ->add('vehicule', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
