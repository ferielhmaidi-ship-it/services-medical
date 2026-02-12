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

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patient', EntityType::class , [
            'class' => Patient::class ,
            'choice_label' => function (Patient $patient) {
            return $patient->getFirstName() . ' ' . $patient->getLastName() . ' (' . $patient->getEmail() . ')';
        },
            'label' => 'Patient',
            'placeholder' => '-- Select a Patient --',
            'required' => true,
            'attr' => ['class' => 'form-select'],
            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
            return $er->createQueryBuilder('p')
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');
        },
        ])
            ->add('appointmentDate', DateType::class , [
            'widget' => 'single_text',
            'label' => 'Appointment Date',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Appointment Date',
            ],
        ])
            ->add('doctor', EntityType::class , [
            'class' => Medecin::class ,
            'choice_label' => function (Medecin $medecin) {
            return 'Dr. ' . $medecin->getFirstName() . ' ' . $medecin->getLastName() . ' — ' . $medecin->getSpecialty();
        },
            'label' => 'Doctor',
            'placeholder' => '-- Select a Doctor --',
            'required' => true,
            'attr' => ['class' => 'form-select'],
            'group_by' => function (Medecin $medecin) {
            return $medecin->getSpecialty();
        },
            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
            return $er->createQueryBuilder('m')
            ->orderBy('m.specialty', 'ASC')
            ->addOrderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC');
        },
        ])
            ->add('message', TextareaType::class , [
            'label' => 'Message (Optional)',
            'required' => false,
            'attr' => [
                'placeholder' => 'Any message or notes...',
                'rows' => 4,
                'class' => 'form-control',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class ,
        ]);
    }
}