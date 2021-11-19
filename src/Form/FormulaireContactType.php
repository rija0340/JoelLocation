<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class FormulaireContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('email', EmailType::class)
            ->add('telephone', TelType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10]),
                ],
            ])
            ->add('adresse', TextType::class, ['constraints' => [
                new NotBlank(),
            ],])
            ->add('message', TextareaType::class, ['constraints' => [
                new NotBlank(),
            ],])
            ->add('objet', ChoiceType::class, [
                'choices'  => [
                    "Demande de devis" => 'Demande de devis',
                    "Demande de renseignement" => 'Demande de renseignement',
                    "Autre" => 'Autre',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
