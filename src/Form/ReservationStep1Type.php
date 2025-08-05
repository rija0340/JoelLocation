<?php

namespace App\Form;

use PhpParser\Node\Expr\BinaryOp\Greater;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Form\DataTransformer\DateTimeStringTransformer;

class ReservationStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new DateTimeStringTransformer();
        
        $builder
            ->add('agenceDepart', ChoiceType::class, [
                'choices'  => [
                    'Aéroport de Point-à-pitre' => 'Aéroport de Point-à-pitre',
                    'Agence du Moule' => 'Agence du Moule',
                    'Gare Maritime de Bergervin' => 'Gare Maritime de Bergervin',
                    'Gare maritime de Saint-François' => 'Gare maritime de Saint-François',
                    'Point de livraison : ' =>
                    [
                        'Abymes' => 'Abymes',
                        'Anse-bertrand' => 'Anse-bertrand',
                        'Gosier' => 'Gosier',
                        'Moule' => 'Moule',
                        "Morne-à-l'Eau" => "Morne-à-l'Eau",
                        "Petit-canal" => "Petit-canal",
                        "Pointe-à-pitre" => "Pointe-à-pitre",
                        "Port-louis" => "Port-louis",
                        "Sainte-anne" => "Sainte-anne",
                        "Saint-François" => "Saint-François",
                    ]
                ],
                'required' => true
            ])
            ->add('dateDepart', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control smart-datetime-input',
                    'placeholder' => 'dd/mm/yyyy hh:mm'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La date de départ est requise']),
                ]
            ])
            ->add('typeVehicule', ChoiceType::class, [
                'choices'  => [
                    'Classic' => 'Classic',
                ],
                'required' => true
            ])
            ->add('agenceRetour', ChoiceType::class, [
                'choices'  => [
                    'Aéroport de Point-à-pitre' => 'Aéroport de Point-à-pitre',
                    'Agence du Moule' => 'Agence du Moule',
                    'Gare Maritime de Bergervin' => 'Gare Maritime de Bergervin',
                    'Gare maritime de Saint-François' => 'Gare maritime de Saint-François',
                    'Point de livraison : ' =>
                    [
                        'Abymes' => 'Abymes',
                        'Anse-bertrand' => 'Anse-bertrand',
                        'Gosier' => 'Gosier',
                        'Moule' => 'Moule',
                        "Morne-à-l'Eau" => "Morne-à-l'Eau",
                        "Petit-canal" => "Petit-canal",
                        "Pointe-à-pitre" => "Pointe-à-pitre",
                        "Port-louis" => "Port-louis",
                        "Sainte-anne" => "Sainte-anne",
                        "Saint-François" => "Saint-François",
                    ]
                ],
                'required' => true
            ])
            ->add('dateRetour', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control smart-datetime-input',
                    'placeholder' => 'dd/mm/yyyy hh:mm'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La date de retour est requise']),
                    new Callback([$this, 'validateReturnDate'])
                ]
            ])
            ->add('lieuSejour', TextType::class, [
                'required' => false
            ]);

        // Add transformers
        $builder->get('dateDepart')->addModelTransformer($transformer);
        $builder->get('dateRetour')->addModelTransformer($transformer);
    }

    public function validateReturnDate($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $departDate = $form->get('dateDepart')->getData();
        
        if ($departDate && $value) {
            if ($value <= $departDate) {
                $context->buildViolation('La date de retour doit être postérieure à la date de départ')
                    ->addViolation();
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}