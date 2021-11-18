<?php

namespace App\Form;

use App\Entity\Tarifs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
            ->add('marque', EntityType::class)
            ->add('modele', EntityType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tarifs::class,
        ]);
    }
}
