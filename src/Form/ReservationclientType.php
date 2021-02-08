<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationclientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            //->add('date_reservation')
            ->add('date_debut')
            ->add('date_fin')
            ->add('lieu')
            //->add('code_reservation')
            //->add('client')
            ->add('vehicule')
            //->add('utilisateur')
            //->add('mode_reservation')
            //->add('etat_reservation')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
