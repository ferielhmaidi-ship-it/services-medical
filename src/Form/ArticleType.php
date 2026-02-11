<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Magazine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('resume')
            ->add('auteur')
            ->add('datePub', null, [
                'widget' => 'single_text',
                'label' => 'Date de publication',
                'attr' => ['class' => 'article-form-control'],
            ])
            ->add('magazine', EntityType::class, [
                'class' => Magazine::class,
                'choice_label' => 'title',
                'label' => 'Magazine associé',
                'placeholder' => 'Choisir un magazine...',
            ])
            ->add('statut', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices'  => [
                    'Brouillon' => 'draft',
                    'Publié' => 'published',
                ],
                'label' => 'Statut de l\'article',
                'attr' => ['class' => 'article-form-control'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'article',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, JPG, PNG, GIF ou WebP)',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
