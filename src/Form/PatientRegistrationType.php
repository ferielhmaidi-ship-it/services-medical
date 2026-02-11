<?php

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
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PatientRegistrationType extends AbstractType
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
                'help' => 'This will be your login email'
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
                'attr' => ['class' => 'form-control'],
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
                    'placeholder' => '123 Main Street, City',
                    'rows' => 3
                ],
                'label' => 'Address',
                'help' => 'Optional'
            ])
            ->add('dateOfBirth', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => date('Y-m-d')
                ],
                'label' => 'Date of Birth',
                'help' => 'Optional - Click to select date'
            ])
            ->add('hasInsurance', CheckboxType::class, [
                'required' => false,
                'label' => 'I have medical insurance',
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
                'help' => 'If you have insurance'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'form-control']],
                'required' => true,
                'first_options'  => [
                    'label' => 'Password *',
                    'attr' => [
                        'placeholder' => 'Minimum 6 characters',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat Password *',
                    'attr' => [
                        'placeholder' => 'Enter the same password again',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must agree to our terms.',
                    ]),
                ],
                'label' => 'I agree to the terms and conditions *',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
            'attr' => ['class' => 'needs-validation', 'novalidate' => 'novalidate']
        ]);
    }
}
