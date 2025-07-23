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
        'label' => 'Votre e-mail',
        'attr' => [
            'class' => 'input input-bordered w-full',
        ],
    ])
    ->add('old_password', PasswordType::class, [
        'mapped' => false,
        'label' => 'Mot de passe actuel',
        'attr' => [
            'class' => 'input input-bordered w-full',
            'placeholder' => 'Veuillez saisir votre mot de passe actuel',
        ],
    ])
    ->add('new_password', RepeatedType::class, [
        'type' => PasswordType::class,
        'mapped' => false,
        'invalid_message' => 'Le mot de passe et la confirmation doivent être identiques',
        'required' => true,
        'first_options' => [
            'label' => 'Nouveau mot de passe',
            'attr' => [
                'class' => 'input input-bordered w-full',
                'placeholder' => 'Saisissez votre nouveau mot de passe',
            ],
        ],
        'second_options' => [
            'label' => 'Confirmation',
            'attr' => [
                'class' => 'input input-bordered w-full',
                'placeholder' => 'Confirmez votre nouveau mot de passe',
            ],
        ],
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
