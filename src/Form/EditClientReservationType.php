<?php

namespace App\Form;

use App\Entity\User;
use App\Form\InfosResaType;
use App\Form\InfosVolResaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class EditClientReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('adresse', TextType::class, [
                'required' => false
            ])
            ->add('complementAdresse', TextType::class, [
                'required' => false
            ])
            ->add('ville', TextType::class, [
                'required' => false
            ])
            ->add('codePostal', NumberType::class, [
                'required' => false
            ])
            ->add('mail', EmailType::class)
            ->add('telephone', TelType::class, [
                'required' => false
            ])
            ->add('portable', TelType::class)
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
