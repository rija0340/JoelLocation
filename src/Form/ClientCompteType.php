<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ClientCompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mail', TextType::class, [
                'disabled' => true,
                'attr' => [
                    'label' => 'Votre e-mail'
                ]
            ])
            ->add('old_password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe actuel ',
                'attr' => [
                    'placeholder' => 'Veuillez saisir votre mot de passe actuel'
                ]
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => ' le mot de passe et la confirmation doivent être identique',
                'required' => true,
                'first_options' => [
                    'label' => 'Nouveau mot de passe ',
                    'attr' => [
                        'placeholder' => 'Veuillez saisir votre nouveau mot de passe'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmation nouveau mot de passe',
                    'attr' => [
                        'placeholder' => 'Veuillez confirmer votre nouveau mot de passe'
                    ]
                ]
            ]);
        // ->add('motdepass', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
