<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null,[
                'label' => "Nom d'utilisateur",
            ])
            //->add('roles')
<<<<<<< HEAD
            ->add('password', PasswordType::class,[
                'label' => "Mot de passe",
            ])
            ->add('nom', null,[
                'label' => "Nom",
            ])
            ->add('prenom', null,[
                'label' => "Prénom",
            ])
            ->add('adresse', null,[
                'label' => "Adresse",
            ])
            ->add('mail', null,[
                'label' => "Email",
            ])
            ->add('telephone', null,[
                'label' => "Téléphone",
            ])
            ->add('portable', null,[
                'label' => "Portable",
            ])
=======
            ->add('password', PasswordType::class)
            ->add('nom')
            ->add('prenom')
            ->add('adresse')
            ->add('mail', EmailType::class)
            ->add('telephone', TelType::class)
            ->add('portable', TelType::class)
>>>>>>> remarque2
            //->add('presence')
            //->add('date_inscription')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
