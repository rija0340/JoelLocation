<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ReservationclientType extends AbstractType
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
            ->add('type')
            //->add('date_reservation')
            ->add('lieu')
            //->add('code_reservation')
            //->add('client')
            // ->add('vehicule')
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
