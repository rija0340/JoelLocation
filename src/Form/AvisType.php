<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('global', NumberType::class)
            ->add('ponctualite', NumberType::class)
            ->add('accueil', NumberType::class)
            ->add('service', NumberType::class)
            ->add('commentaire', TextType::class)
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('reservation', EntityType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
