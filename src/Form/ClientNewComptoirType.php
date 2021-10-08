<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ClientNewComptoirType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', null, [
                'required' => true,
                'attr'  => [
                    'class' => 'form-control'
                ]
            ])
            ->add('prenom', null, [
                'required' => true,
                'attr'  => [
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr'  => [
                    'class' => 'form-control'
                ]
            ])
            ->add('telephone', TelType::class, [
                'required' => true,
                'label' => 'Téléphone',
                'attr'  => [
                    'class' => 'form-control',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
