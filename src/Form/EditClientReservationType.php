<?php

namespace App\Form;

use App\Entity\User;
use App\Form\InfosResaType;
use App\Form\InfosVolResaType;
use App\Validator\AtLeastOneField;
use Doctrine\DBAL\Types\TextType as TypesTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EditClientReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('sexe', ChoiceType::class, [
                'choices'  => [
                    'Mr' => 'Mr',
                    'Mlle' => 'Mlle',
                    'Mme' => 'Mme',
                ],
                'required' => true
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('lieuNaissance', TextType::class)
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
                'required' => false,
                'empty_data' => ""
            ])
            ->add('portable', TelType::class, [
                'required' => false,  // Changed this to false
                'empty_data' => ""    // Added this
            ])
            ->add('infosResa', InfosResaType::class)
            ->add('infosVolResa', InfosVolResaType::class)
            ->add('numeroPermis', TextType::class)
            ->add('datePermis', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('villeDelivrancePermis', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    // public function validate($data, ExecutionContextInterface $context)
    // {
    //     if (empty($data['portable']) && empty($data['telephone'])) {
    //         $context->buildViolation('At least one of field1 or field2 must be filled.')
    //             ->atPath('telephone')
    //             ->addViolation();
    //     }
    // }
}
