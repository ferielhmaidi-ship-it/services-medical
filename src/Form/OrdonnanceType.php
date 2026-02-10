<?php

namespace App\Form;

use App\Entity\Ordonnance;
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

            ->add('idrendezvous', TextType::class, [
                'mapped' => false,
                'label' => 'Date rendez-vous',
                'data' => $options['is_mod'] && $ordonnance && $ordonnance->getIdrendezvous()
                    ? $ordonnance->getIdrendezvous()->getDate()->format('Y-m-d\TH:i')
                    : null,
                'attr' => [
                    'list' => 'rendezvous_list',
                    'type' => 'datetime-local',
                ],
            ])

            ->add('idmedecin', TextType::class, [
                'mapped' => false,
                'label' => 'Médecin',
                'data' => $options['is_mod'] && $ordonnance && $ordonnance->getIdmedecin()
                    ? $ordonnance->getIdmedecin()->getNom()
                    : null,
                'attr' => [
                    'list' => 'medecin_list',
                ],
            ])

            ->add('idpatient', TextType::class, [
                'mapped' => false,
                'label' => 'Patient',
                'data' => $options['is_mod'] && $ordonnance && $ordonnance->getIdpatient()
                    ? $ordonnance->getIdpatient()->getNom().' '.$ordonnance->getIdpatient()->getPrenom()
                    : null,
                'attr' => [
                    'list' => 'patient_list',
                ],
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
