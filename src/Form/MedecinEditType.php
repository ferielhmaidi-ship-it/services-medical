<?php
// src/Form/MedecinEditType.php

namespace App\Form;

use App\Entity\Medecin;
use App\Constants\Governorate;
use App\Constants\Specialty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class MedecinEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'doctor@example.com'
                ],
                'label' => 'Email Address *',
                'help' => 'This will be the login email'
            ])
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'John'
                ],
                'label' => 'First Name *'
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Doe'
                ],
                'label' => 'Last Name *'
            ])
            ->add('age', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '30'
                ],
                'label' => 'Age',
                'help' => 'Optional'
            ])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Select Gender' => '',
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other',
                ],
                'attr' => ['class' => 'form-control select2'],
                'label' => 'Gender'
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+216 12 345 678'
                ],
                'label' => 'Phone Number',
                'help' => 'Optional'
            ])
            ->add('specialty', ChoiceType::class, [
                'choices' => Specialty::getChoices(),
                'placeholder' => 'Select your medical specialty *',
                'attr' => ['class' => 'form-control select2'],
                'label' => 'Medical Specialty *',
                'help' => 'Choose your primary medical specialty',
            ])
            ->add('cin', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '12345678'
                ],
                'label' => 'CIN (8 digits) *',
                'help' => 'Exactly 8 digits, no letters or spaces',
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'CIN must be exactly 8 digits',
                    ]),
                    new Regex([
                        'pattern' => '/^\d{8}$/',
                        'message' => 'CIN must contain only digits (0-9)',
                    ]),
                ],
            ])
            ->add('address', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '123 Medical Street, City, Postal Code',
                    'rows' => 3
                ],
                'label' => 'Clinic/Hospital Address'
            ])
            ->add('governorate', ChoiceType::class, [
                'required' => false,
                'choices' => Governorate::getChoices(),
                'placeholder' => 'Select Governorate',
                'attr' => ['class' => 'form-control select2'],
                'label' => 'Governorate'
            ])
            ->add('education', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Example: MD from University of Medicine, Residency in...',
                    'rows' => 4
                ],
                'label' => 'Education Background',
                'help' => 'Degrees, universities, years'
            ])
            ->add('experience', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Example: 10 years of experience in cardiology, worked at...',
                    'rows' => 4
                ],
                'label' => 'Professional Experience',
                'help' => 'Years of experience, specializations, previous positions'
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Account is active',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'Account is verified',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'form-control']],
                'required' => $options['is_new'],
                'first_options'  => [
                    'label' => $options['is_new'] ? 'Password *' : 'New Password',
                    'attr' => [
                        'placeholder' => $options['is_new'] ? 'Minimum 6 characters' : 'Leave blank to keep current password',
                        'autocomplete' => 'new-password'
                    ],
                    'help' => 'Minimum 6 characters'
                ],
                'second_options' => [
                    'label' => $options['is_new'] ? 'Repeat Password *' : 'Repeat Password',
                    'attr' => [
                        'placeholder' => $options['is_new'] ? 'Enter same password again' : 'Leave blank to keep current password',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'mapped' => false,
                'constraints' => $options['is_new'] ? [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ] : [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
            'is_new' => false,
            'attr' => ['class' => 'needs-validation', 'novalidate' => 'novalidate']
        ]);
        $resolver->setAllowedTypes('is_new', 'bool');
    }
}
