<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RechercheAVType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $typeDate = [
            'Date de réservation' => 'dateReservation',
            'Date de départ' => 'dateDepart',
            'Date de retour' => 'dateRetour'
        ];

        $typeTarifs = [
            'Internet' => 'WEB',
            'Comptoir' => 'CPT'
        ];


        $builder
            ->add('typeDate', ChoiceType::class, [
                'placeholder' => 'Choisir un type de date',
                'choices' => $typeDate,
            ])
            ->add('debutPeriode', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('finPeriode', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('categorie', EntityType::class, [
                'placeholder' => 'Choisir une catégorie',
                'class' => Vehicule::class,
                'required' => false
            ])
            ->add('typeTarif', ChoiceType::class, [

                'placeholder' => 'Choisir un type de tarif',
                'choices' => $typeTarifs,
                'required' => false
            ])
            ->add('codePromo', TextType::class, [
                // 'placeholder' => 'Code promo',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
