<?php

namespace App\Form;

use App\Entity\Devis;
use App\Entity\Options;
use App\Entity\Garantie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('id', HiddenType::class)
            ->add('options', EntityType::class, [
                'class' => Options::class,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('garanties', EntityType::class, [
                'class' => Garantie::class,
                'multiple' => true,
                'expanded' => true,
                // 'data' => true,
                // 'choice_label' => 't'
                // 'choice_label' => 'description',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
