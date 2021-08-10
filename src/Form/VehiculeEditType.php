<?php

namespace App\Form;

use App\Entity\Modele;
use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class VehiculeEditType extends AbstractType
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
            ->add('prix_acquisition')
            ->add(
                'marque'
            )
            ->add('type')
            ->add('modele', HiddenType::class)
            ->add('details')
            ->add('carburation')
            ->add('caution')
            ->add('vitesse')
            ->add('bagages')
            ->add('portes')
            ->add('passagers')
            ->add('atouts')
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
