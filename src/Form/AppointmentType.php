<?php

namespace App\Form;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => function(Patient $patient) {
                    return $patient->getFirstName() . ' ' . $patient->getLastName() . ' (' . $patient->getEmail() . ')';
                },
                'label' => false,
                'placeholder' => 'Select Patient',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.lastName', 'ASC')
                        ->addOrderBy('p.firstName', 'ASC');
                },
            ])
            ->add('appointmentDate', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'attr' => [
                    'class' => 'form-control datepicker',
                    'placeholder' => 'Appointment Date'
                ]
            ])
            ->add('doctor', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => function(Medecin $medecin) {
                    return 'Dr. ' . $medecin->getFirstName() . ' ' . $medecin->getLastName() . ' (' . $medecin->getSpecialite() . ')';
                },
                'label' => false,
                'placeholder' => 'Select Doctor',
                'attr' => ['class' => 'form-select'],
                'group_by' => function(Medecin $medecin) {
                    return $medecin->getSpecialite();
                }
            ])
            ->add('message', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Message (Optional)',
                    'rows' => 5,
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}