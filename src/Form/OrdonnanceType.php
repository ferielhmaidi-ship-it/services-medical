<?php

namespace App\Form;

use App\Entity\Medecin;
use App\Entity\Ordonnance;
use App\Entity\Patient;
use App\Entity\Appointment;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class OrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateordonnance', DateTimeType::class, [
                'label' => 'Date ordonnance',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new NotNull(['message' => 'La date de prescription est obligatoire.']),
                ],
            ])
            ->add('diagnosis', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Le diagnostic est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le diagnostic ne doit pas depasser {{ limit }} caracteres.',
                    ]),
                ],
            ])
            ->add('medicament', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Les medicaments prescrits sont obligatoires.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le champ medicaments ne doit pas depasser {{ limit }} caracteres.',
                    ]),
                ],
            ])
            ->add('posologie', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'La posologie est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'La posologie ne doit pas depasser {{ limit }} caracteres.',
                    ]),
                ],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('instructions', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('rendezVous', EntityType::class, [
                'property_path' => 'appointment',
                'class' => Appointment::class,
                'choice_label' => static function (Appointment $appointment): string {
                    $date = $appointment->getDate()?->format('Y-m-d') ?? 'Date inconnue';
                    $time = $appointment->getStartTime()?->format('H:i') ?? '--:--';

                    return sprintf('RDV #%d - %s %s', $appointment->getId() ?? 0, $date, $time);
                },
                'query_builder' => static function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('r')
                        ->orderBy('r.date', 'DESC')
                        ->addOrderBy('r.startTime', 'DESC');

                    if ($options['medecin'] instanceof Medecin) {
                        $qb->andWhere('r.doctorId = :doctorId')
                           ->setParameter('doctorId', $options['medecin']->getId());
                    }
                    if ($options['patient'] instanceof Patient) {
                        $qb->andWhere('r.patientId = :patientId')
                           ->setParameter('patientId', $options['patient']->getId());
                    }

                    return $qb;
                },
                'label' => 'Rendez-vous',
                'placeholder' => 'Choisir un rendez-vous',
                'constraints' => [
                    new NotNull(['message' => 'Le choix du rendez-vous est obligatoire.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ordonnance::class,
            'is_mod' => false,
            'medecin' => null,
            'patient' => null,
        ]);

        $resolver->setAllowedTypes('medecin', ['null', Medecin::class]);
        $resolver->setAllowedTypes('patient', ['null', Patient::class]);
    }
}
