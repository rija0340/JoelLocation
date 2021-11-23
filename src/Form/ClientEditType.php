<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\InfosResa;
use App\Form\InfosVolResaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ClientEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('adresse', TextType::class)
            ->add('mail', EmailType::class, [
                'invalid_message' => 'Cette adresse mail est déjà utilisée'
            ])
            ->add('telephone', TelType::class, [
                'required' => true,
                'constraints' => [new Length(['min' => 10])],
            ])
            ->add('portable', TelType::class, [
                'required' => false,
                'constraints' => [new Length(['min' => 10])],
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('numeroPermis', TextType::class)
            ->add('datePermis', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('lieuNaissance', TextType::class)
            ->add('complementAdresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('codePostal', NumberType::class)
            ->add('villeDelivrancePermis', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
