<?php

namespace App\Form;

use App\Entity\Marque;
use App\Entity\Modele;
use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('immatriculation', TextType::class)
            ->add('date_mise_service', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('date_mise_location', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('prix_acquisition', NumberType::class)
            ->add('marque', EntityType::class, ['class' => Marque::class])
            ->add('modele', EntityType::class, ['class' => Modele::class])
            ->add('details', TextareaType::class)
            ->add('carburation', TextType::class)
            ->add('caution', NumberType::class)
            ->add('vitesse', TextType::class)
            ->add('bagages', TextType::class)
            ->add('portes', TextType::class)
            ->add('passagers', TextType::class)
            ->add('atouts', TextType::class)
            ->add('imageFile', VichFileType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
