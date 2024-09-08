<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'label' => false, // Masque le label
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Entrez votre email' 
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Entrez votre Mot de Passe' 
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => false,
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Nom' 
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => false,
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Prénom' 
                ],
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date de Naissance',
                'widget' => 'single_text',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4'
                ],
            ])
            ->add('enrollmentDate', DateType::class, [
                'label' => 'Date d\'optention de Licence',
                'widget' => 'single_text',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4'
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Entrez votre description'
                ],
            ])
            ->add('profilePic', FileType::class, [
                'label' => 'Photo de profile',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Image()
                ]
        ])
            ->add('Inscription', SubmitType::class, [
                'attr' => [
                    'class' => 'bg-svg hover:bg-hoversvg text-white font-bold py-2 px-4 rounded',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
