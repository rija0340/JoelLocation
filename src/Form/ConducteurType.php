<?php

namespace App\Form;

use App\Entity\Conducteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ConducteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('villeNaissance')
            ->add('numeroPermis')
            ->add('villeDelivrance')
            ->add('dateDelivrance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('dateObtention', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Conducteur::class,
        ]);
    }
}
