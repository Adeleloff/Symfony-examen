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
        // On récupère l'option 'is_edit' pour savoir si c'est un formulaire de modification
        $isEdit = $options['is_edit'] ?? false;
        
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
                'choice_label' => 'lastName',
            ])
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'name',
            ]);

        // Si ce n'est pas un formulaire d'édition, on ajoute le champ d'ajout
        if (!$isEdit) {
            $builder->add('Ajouter', SubmitType::class, [
                'attr' => [
                    'class' => 'bg-svg hover:bg-hoversvg font-bold py-2 px-4 rounded',
                ],
            ]);
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'is_edit' => false,
        ]);

        $resolver->setDefined(['is_edit']);
    }
}
