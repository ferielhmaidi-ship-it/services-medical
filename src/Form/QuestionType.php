<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Specialite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du Question',
                'constraints' => [
                    new NotBlank(['message' => 'Le titre ne peut pas être vide'])
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Contenu du Question',
                'constraints' => [
                    new NotBlank(['message' => 'La description ne peut pas être vide'])
                ],
            ])
            ->add('specialite', EntityType::class, [
                'class' => Specialite::class,
                'choice_label' => 'nom',
                'label' => 'Spécialité',
                'placeholder' => 'Choisir une spécialité',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
