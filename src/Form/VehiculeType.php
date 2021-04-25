<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('immatriculation')
            ->add('date_mise_service', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('date_mise_location')
            ->add('modele')
            ->add('prix_acquisition')
            ->add('tarif_journaliere')
            ->add('marque')
            ->add('type')
            ->add('details')
            ->add('carburation')
            ->add('caution')
            ->add('vitesse')
            ->add('bagages')
            ->add('portes')
            ->add('passagers')
            ->add('atouts')
            ->add('image', FileType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
