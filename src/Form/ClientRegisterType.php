<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class ClientRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => new Length([
                    'min' => 2,
                    'max' => 20
                ]),
            ])
            ->add('nom')
            ->add('prenom')
            ->add('adresse')
            ->add('mail', EmailType::class)
            ->add('telephone', TelType::class)
            ->add('portable', TelType::class)
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('numeroPermis')
            ->add('datePermis', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('lieuNaissance')
            ->add('complementAdresse')
            ->add('ville')
            ->add('codePostal', TextType::class)
            ->add('villeDelivrancePermis')
            ->add('password', RepeatedType::class, [

                'type' => PasswordType::class,
                'invalid_message' => ' le mot de passe et la confirmation doivent être identique',
                'required' => true,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation mot de passe']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
