<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\InfosResa;
use App\Form\InfosVolResaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ClientEditType extends AbstractType
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
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('lieuNaissance')
            ->add('numeroPermis')
            ->add('datePermis', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('villeDelivrancePermis');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
