<?php

namespace App\Form;

use App\Entity\Marque;
use App\Entity\Modele;
use App\Entity\Tarifs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class TarifEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('troisJours', NumberType::class)
            ->add('septJours', NumberType::class)
            ->add('quinzeJours', NumberType::class)
            ->add('trenteJours', NumberType::class)
            ->add('mois', TextType::class)
            ->add('marque', EntityType::class, [
                'class' => Marque::class
            ])
            ->add('modele', EntityType::class, [
                'class' => Modele::class

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tarifs::class,
        ]);
    }
}
