<?php

namespace App\Form;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Rapport;
use App\Entity\Appointment;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class RapportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consultation_reason', TextType::class, [
                'property_path' => 'consultationReason',
                'label' => 'Raison de consultation',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Le motif de consultation est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le motif de consultation ne doit pas depasser {{ limit }} caracteres.',
                    ]),
                ],
            ])
            ->add('diagnosis', TextType::class, [
                'label' => 'Diagnostic',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Le diagnostic principal est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le diagnostic ne doit pas depasser {{ limit }} caracteres.',
                    ]),
                ],
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observations',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Les observations cliniques sont obligatoires.']),
                ],
            ])
            ->add('recommendations', TextareaType::class, [
                'label' => 'Recommandations',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Les recommandations sont obligatoires.']),
                ],
            ])
            ->add('treatments', TextareaType::class, [
                'label' => 'Traitements',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Les traitements prescrits sont obligatoires.']),
                ],
            ])
            ->add('rendezVous', EntityType::class, [
                'property_path' => 'appointment',
                'class' => Appointment::class,
                'choice_label' => static function (Appointment $appointment): string {
                    $date = $appointment->getDate()?->format('d/m/Y') ?? 'Date inconnue';
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
                'placeholder' => 'Selectionnez un rendez-vous',
                'constraints' => [
                    new NotNull(['message' => 'Le choix du rendez-vous est obligatoire.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rapport::class,
            'is_mod' => false,
            'medecin' => null,
            'patient' => null,
        ]);

        $resolver->setAllowedTypes('medecin', ['null', Medecin::class]);
        $resolver->setAllowedTypes('patient', ['null', Patient::class]);
    }
}
