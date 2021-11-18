<?php

namespace App\Form;

use App\Entity\User;
use App\Form\InfosResaType;
use App\Form\InfosVolResaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
<<<<<<< HEAD
use Symfony\Component\Form\Extension\Core\Type\EmailType;
=======
>>>>>>> 37244a7bb5a651844d1ccce329a73d4621efa340
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class EditClientReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
<<<<<<< HEAD
            ->add('adresse', Texttype::class)
            ->add('complementAdresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('codePostal', NumberType::class)
            ->add('mail', EmailType::class)
            ->add('telephone', TelType::class)
            ->add('portable', TelType::class)
=======
            ->add('adresse', TextType::class)
            ->add('complementAdresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('codePostal', NumberType::class)
            ->add('mail', TextType::class)
            ->add('telephone', TextType::class)
            ->add('portable', TextType::class)
>>>>>>> 37244a7bb5a651844d1ccce329a73d4621efa340
            ->add('infosResa', InfosResaType::class)
            ->add('infosVolResa', InfosVolResaType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
