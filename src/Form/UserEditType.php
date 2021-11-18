<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = [
            'Client' => 'ROLE_CLIENT',
            'Employé' => 'ROLE_PERSONNEL',
            'Administrateur' => 'ROLE_ADMIN'
        ];
        $builder
            ->add('fonction', ChoiceType::class, [
                'choices' => $roles,
                'label' => "Rôle de l\'utilisateur",
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('adresse', TextType::class)
            ->add('mail', TextType::class)
            ->add('telephone', TextType::class)
            ->add('portable', TextType::class)
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('lieuNaissance', TextType::class)
            ->add('numeroPermis', TextType::class)
            ->add('datePermis', DateType::class, [
                'widget' => 'single_text',
                'required' => false

            ])
            // ->add('role')
            /* ->add('recupass', HiddenType::class, [
            'required' => true,
            'empty_data' => function ($user) {
                    return $user->getPassword();
                },
        ]) */
            //->add('presence')
            /* ->add('date_inscription', DateTimeType::class, [
            'widget' => 'single_text',
        ]) */;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
