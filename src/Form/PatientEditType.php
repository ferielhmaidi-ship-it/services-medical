<?php
// src/Form/PatientEditType.php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PatientEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'patient@example.com'
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
            ->add('address', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '123 Main Street, City, Postal Code',
                    'rows' => 3
                ],
                'label' => 'Address'
            ])
            ->add('dateOfBirth', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date of Birth',
                'html5' => true,
                'format' => 'yyyy-MM-dd',
            ])
            ->add('hasInsurance', CheckboxType::class, [
                'required' => false,
                'label' => 'Has Insurance',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('insuranceNumber', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'INS-123456'
                ],
                'label' => 'Insurance Number',
                'help' => 'Optional'
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Account is active',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'form-control']],
                'required' => false,
                'first_options'  => [
                    'label' => 'New Password',
                    'attr' => [
                        'placeholder' => 'Leave blank to keep current password',
                        'autocomplete' => 'new-password'
                    ],
                    'help' => 'Minimum 6 characters'
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'placeholder' => 'Leave blank to keep current password',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'mapped' => false,
                'constraints' => [
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
            'data_class' => Patient::class,
            'attr' => ['class' => 'needs-validation', 'novalidate' => 'novalidate']
        ]);
    }
}
