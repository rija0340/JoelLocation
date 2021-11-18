<?php

namespace App\Form;

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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('immatriculation')
            ->add('date_mise_service', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('date_mise_location', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('prix_acquisition', NumberType::class)
            ->add('marque', EntityType::class)
            ->add('type', EntityType::class)
            ->add('details', TextareaType::class)
            ->add('carburation', Text::class)
            ->add('caution', NumberType::class)
            ->add('vitesse', Text::class)
            ->add('bagages', Text::class)
            ->add('portes', Text::class)
            ->add('passagers', Text::class)
            ->add('atouts', Text::class)
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
