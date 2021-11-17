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
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ClientRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('username', TextType::class, [
            //     'constraints' => new Length([
            //         'min' => 2,
            //         'max' => 20
            //     ]),
            // ])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('adresse', TextType::class)
            ->add('mail', EmailType::class, [
                'invalid_message' => 'Cette adresse mail est déjà utilisée'
            ])
            ->add('telephone', TelType::class, [
                'required' => true
            ])
            ->add('portable', TelType::class, [
                'required' => false
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('numeroPermis', TextType::class)
            ->add('datePermis', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('lieuNaissance', TextType::class)
            ->add('complementAdresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('codePostal', NumberType::class)
            ->add('villeDelivrancePermis', TextType::class)
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
