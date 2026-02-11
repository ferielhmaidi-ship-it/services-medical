<?php

namespace App\Form;

use App\Entity\Magazine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class MagazineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Magazine title',
                ],
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Short description',
                    'rows' => 4,
                ],
            ])
            ->add('dateCreate', null, [
                'label' => 'Created date',
                'widget' => 'single_text',
            ])
            ->add('statut', null, [
                'label' => 'Status',
                'attr' => [
                    'placeholder' => 'Draft / Published',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du magazine',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',      // .jpeg
                            'image/jpg',       // .jpg
                            'image/png',       // .png
                            'image/gif',       // .gif
                            'image/webp',      // .webp
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, JPG, PNG, GIF ou WebP)',
                    ])
                ],
            ])
            ->add('pdfFileUpload', FileType::class, [
                'label' => 'Fichier PDF du magazine',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                        'application/pdf',
            ],
            'mimeTypesMessage' => 'Veuillez uploader un PDF valide',
        ])
    ],
])

        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Magazine::class,
        ]);
    }
}
