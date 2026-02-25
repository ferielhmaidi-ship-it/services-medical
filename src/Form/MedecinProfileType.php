<?php
// src/Form/MedecinProfileType.php

namespace App\Form;

use App\Entity\Medecin;
use App\Constants\Specialty;
use App\Constants\Governorate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class MedecinProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire']),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                ],
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 18, 'max' => 100],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre',
                'required' => false,
                'choices' => [
                    'Homme' => 'M',
                    'Femme' => 'F',
                    'Autre' => 'O',
                ],
                'placeholder' => 'Sélectionnez votre genre',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('specialty', ChoiceType::class, [
                'label' => 'Spécialité médicale',
                'choices' => $this->getGroupedSpecialties(),
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                },
                'group_by' => function($choice, $key, $value) {
                    return $this->getSpecialtyGroup($choice);
                },
                'placeholder' => 'Sélectionnez votre spécialité',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'La spécialité est obligatoire']),
                ],
            ])
            ->add('cin', TextType::class, [
                'label' => 'CIN',
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length(['min' => 8, 'max' => 20]),
                    new Regex([
                        'pattern' => '/^[\d\s\+\-\(\)]+$/',
                        'message' => 'Numéro de téléphone invalide'
                    ]),
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('education', TextareaType::class, [
                'label' => 'Formation',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'help' => 'Listez vos diplômes et formations',
            ])
            ->add('experience', TextareaType::class, [
                'label' => 'Expérience professionnelle',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'help' => 'Décrivez votre parcours professionnel',
            ])
            ->add('governorate', ChoiceType::class, [
                'label' => 'Gouvernorat',
                'required' => false,
                'choices' => Governorate::getChoices(),
                'placeholder' => 'Sélectionnez votre gouvernorat',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Compte actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Compte vérifié',
                'required' => false,
                'disabled' => true,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
        ]);
    }

    private function getGroupedSpecialties(): array
    {
        $choices = [];
        foreach (Specialty::getChoices() as $label => $value) {
            $choices[$label] = $value;
        }
        return $choices;
    }

    private function getSpecialtyGroup(string $specialty): ?string
    {
        $groups = Specialty::getGroups();
        foreach ($groups as $groupName => $specialties) {
            if (in_array($specialty, $specialties)) {
                return $groupName;
            }
        }
        return null;
    }
}
