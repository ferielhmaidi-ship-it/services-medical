<?php

namespace App\Form;

use App\Entity\Rapport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RapportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rapport = $builder->getData();

        $builder
            ->add('consultation_reason', TextType::class)
            ->add('diagnosis', TextType::class)
            ->add('observations', TextareaType::class)
            ->add('recommendations', TextareaType::class)
            ->add('treatments', TextareaType::class)

            ->add('idrendezvous', TextType::class, [
                'mapped' => false,
                'label' => 'Date rendez-vous',
                'data' => $options['is_mod'] && $rapport->getIdrendezvous()
                          ? $rapport->getIdrendezvous()->getDate()->format('Y-m-d H:i')
                          : null,
                'attr' => [
                    'list' => 'rendezvous_list',
                    'placeholder' => $options['is_mod'] ? null : 'Tapez date rendez-vous',
                    'type' => 'datetime-local',
                ],
            ])
            ->add('idmedecin', TextType::class, [
                'mapped' => false,
                'label' => 'Médecin',
                'data' => $options['is_mod'] && $rapport->getIdmedecin()
                          ? $rapport->getIdmedecin()->getNom()
                          : null,
                'attr' => [
                    'list' => 'medecin_list',
                    'placeholder' => $options['is_mod'] ? null : 'Tapez nom médecin',
                ],
            ])
            ->add('idpatient', TextType::class, [
                'mapped' => false,
                'label' => 'Patient',
                'data' => $options['is_mod'] && $rapport->getIdpatient()
                          ? $rapport->getIdpatient()->getNom().' '.$rapport->getIdpatient()->getPrenom()
                          : null,
                'attr' => [
                    'list' => 'patient_list',
                    'placeholder' => $options['is_mod'] ? null : 'Tapez nom patient',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rapport::class,
            'is_mod' => false,
        ]);
    }
}
