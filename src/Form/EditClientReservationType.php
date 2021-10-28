<?php

namespace App\Form;

use App\Entity\User;
use App\Form\InfosResaType;
use App\Form\InfosVolResaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditClientReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('adresse')
            ->add('complementAdresse')
            ->add('ville')
            ->add('codePostal')
            ->add('mail')
            ->add('telephone')
            ->add('portable')
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
