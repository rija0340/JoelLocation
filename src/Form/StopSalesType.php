<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StopSalesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class)
            // ->add('date_reservation')
            ->add('date_debut', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('date_fin', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('vendu', CheckboxType::class, [
                'mapped' => false,
                'required' => false,

                'attr' => ['class' => 'form-check-control'],
            ])
            ->add('dateVente', DateType::class, [
                'widget' => 'single_text',
                'mapped' => false,
                'required' => false
            ])
            // ->add('lieu')
            // ->add('code_reservation')
            // ->add('conducteur')
            // ->add('siege')
            // ->add('garantie')
            ->add('commentaire', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
            ]);
        // ->add('client')
        // ->add('vehicule', HiddenType::class);
        // ->add('mode_reservation')
        // ->add('etat_reservation');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
