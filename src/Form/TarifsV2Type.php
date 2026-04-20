<?php

namespace App\Form;

use App\Entity\TarifsV2;
use App\Entity\Marque;
use App\Entity\Modele;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TarifsV2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('marque', EntityType::class, [
                'class' => Marque::class,
                'choice_label' => 'libelle',
                'label' => 'Marque',
            ])
            ->add('modele', EntityType::class, [
                'class' => Modele::class,
                'choice_label' => 'libelle',
                'label' => 'Modèle',
            ])
            ->add('mois', ChoiceType::class, [
                'choices' => [
                    'Janvier' => 'Janvier',
                    'Février' => 'Février',
                    'Mars' => 'Mars',
                    'Avril' => 'Avril',
                    'Mai' => 'Mai',
                    'Juin' => 'Juin',
                    'Juillet' => 'Juillet',
                    'Août' => 'Août',
                    'Septembre' => 'Septembre',
                    'Octobre' => 'Octobre',
                    'Novembre' => 'Novembre',
                    'Décembre' => 'Décembre',
                ],
                'label' => 'Mois',
            ])
            ->add('tarifs', CollectionType::class, [
                'entry_type' => TarifRangeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Plages de tarifs',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TarifsV2::class,
        ]);
    }
}
