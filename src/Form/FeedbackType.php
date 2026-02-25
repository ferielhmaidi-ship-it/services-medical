<?php

namespace App\Form;

use App\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    '⭐ 1 Star - Poor' => 1,
                    '⭐⭐ 2 Stars - Fair' => 2,
                    '⭐⭐⭐ 3 Stars - Good' => 3,
                    '⭐⭐⭐⭐ 4 Stars - Very Good' => 4,
                    '⭐⭐⭐⭐⭐ 5 Stars - Excellent' => 5,
                ],
                'label' => 'Rate your experience',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Your feedback',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Share your experience with the doctor (minimum 10 characters)...',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
