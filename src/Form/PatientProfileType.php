<?php
// src/Form/PatientProfileType.php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class PatientProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre prénom'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'votre@email.com'
                ]
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+216 XX XXX XXX'
                ],
                'constraints' => [
                    new Length(['max' => 20]),
                    new Regex([
                        'pattern' => '/^[\d\s\+\-\(\)]+$/',
                        'message' => 'Numéro de téléphone invalide'
                    ])
                ]
            ])
            ->add('age', TextType::class, [
                'label' => 'Âge',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre âge'
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                    'Autre' => 'Autre'
                ],
                'required' => false,
                'placeholder' => 'Sélectionnez votre genre',
                'attr' => ['class' => 'form-select']
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => date('Y-m-d')
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre adresse complète',
                    'rows' => 3
                ]
            ])
            ->add('hasInsurance', CheckboxType::class, [
                'label' => 'J\'ai une assurance médicale',
                'required' => false,
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('insuranceNumber', TextType::class, [
                'label' => 'Numéro d\'assurance',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre numéro d\'assurance'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour le profil',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
        ]);
    }
}
