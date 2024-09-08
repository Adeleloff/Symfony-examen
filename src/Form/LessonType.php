<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Entity\SubCategory;
use App\Entity\Teacher;
use App\Validator\Constraints\Video;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false, // Masque le label
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Titre du cours'
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Entrez votre cours ici' // Ajoute un placeholder
                ],
            ])
            ->add('lessonVideo', FileType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Video([
                        'maxSize' => '5120M',  // 5 Go pour les vidéos
                    ])
                ]
            ])
            ->add('visible')
            ->add('teacher', EntityType::class, [
                'class' => Teacher::class,
                'choice_label' => 'id',
            ])
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'id',
            ])
            ->add('Ajouter', SubmitType::class, [
                'attr' => [
                    'class' => 'bg-svg hover:bg-hoversvg font-bold py-2 px-4 rounded',
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}
