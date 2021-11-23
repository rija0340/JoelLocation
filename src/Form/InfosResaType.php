<?php

namespace App\Form;

use App\Entity\InfosResa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InfosResaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nbrAdultes', NumberType::class, [
                'required' => false
            ])
            ->add('nbrEnfants', NumberType::class, [
                'required' => false
            ])
            ->add('nbrBebes', NumberType::class, [
                'required' => false
            ])
            ->add('infosInternes', TextareaType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InfosResa::class,
        ]);
    }
}
