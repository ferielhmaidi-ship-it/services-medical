<?php

namespace App\Form;

use App\Entity\Ordonnance;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ordonnance = $builder->getData();

        $builder
            ->add('dateordonnance', DateTimeType::class, [
                'label' => 'Date ordonnance',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('diagnosis', TextType::class)
            ->add('medicament', TextType::class)
            ->add('posologie', TextType::class)
            ->add('notes', TextareaType::class)
            ->add('instructions', TextareaType::class)

            // Ø±Ø¨Ø· Ù…Ø¨Ø§Ø´Ø±Ø© Ù…Ø¹ RendezVous Ø¨Ø¯Ù„ idrendezvous
            ->add('rendezVous', EntityType::class, [
                'class' => RendezVous::class,
                'choice_label' => function(RendezVous $rv) {
                    return $rv->getPatient()->getFullName() . ' - ' . $rv->getAppointmentDate()->format('Y-m-d H:i');
                },
                'label' => 'Rendez-vous',
                'placeholder' => 'Choisir un rendez-vous',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ordonnance::class,
            'is_mod' => false,
        ]);
    }
}

