<?php

namespace App\Form;

use App\Entity\Magazine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre du magazine',
                ],
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description courte',
                    'rows' => 4,
                ],
            ])
            ->add('dateCreate', null, [
                'label' => 'Date de création',
                'widget' => 'single_text',
            ])
            ->add('statut', ChoiceType::class, [
                'choices'  => [
                    'Brouillon' => 'draft',
                    'Publié' => 'published',
                    'Archivé' => 'archived',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du magazine',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo.',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/jpg,image/png,image/gif,image/webp',
                ],
            ])
            ->add('pdfFileUpload', FileType::class, [
                'label' => 'Fichier PDF du magazine',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'maxSizeMessage' => 'Le PDF ne doit pas dépasser 10 Mo.',
                    ])
                ],
                'attr' => [
                    'accept' => 'application/pdf',
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
