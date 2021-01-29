<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null,[
                'label' => "Nom d'utilisateur",
            ])
            ->add('roles', null,[
                'label' => "Rôles de l'utilisateur",
            ])
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
            ->add('presence', null,[
                'label' => "Présence",
            ])
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
