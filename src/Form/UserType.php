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
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère l'option 'is_edit' pour savoir si c'est un formulaire de modification
        $isEdit = $options['is_edit'] ?? false;
        // On récupère l'option 'is_user' pour savoir si c'est un formulaire est pour un utilisateur
        $isUser = $options['is_user'] ?? false;

        $builder->add('email', TextType::class, [
                    'label' => false,
                    'required' => true,
                    'attr' => [
                        'class' => 'border text-black border-gray-300 p-2 rounded w-full mb-4',
                        'placeholder' => 'Entrez votre email' 
                    ],
                ]);
    
        // Si ce n'est pas un formulaire d'édition, on ajoute le champ de mot de passe
        if (!$isEdit) {
            $builder->add('password', PasswordType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded w-full mb-4',
                    'placeholder' => 'Entrez votre mot de passe'
                ],
            ])
            
                ->add('confirmPassword', PasswordType::class, [
                    'label' => 'Confirmez le nouveau mot de passe',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez confirmer votre nouveau mot de passe',
                        ]),
                    ],
                    'mapped' => false,
                ]);
        }

        //seulement si c'est un User qu'on veut créer
        if ($isUser) {
            $builder->add('creation', SubmitType::class, [
                'label' => 'Création', 
                'attr' => [
                    'class' => 'bg-svg hover:bg-hoversvg text-black font-bold py-2 px-4 rounded',
                ],
            ]);
        }
        

        //Si ce n'est pas un user
        if (!$isUser) {
            
            $builder
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
                    'label' => 'Date d\'obtention de Licence',
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
                    'label' => 'Photo de profil',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new Image()
                    ]
                    ]);
    
            // Si ce n'est pas un formulaire d'édition, on ajoute le champ d'inscription
            if (!$isEdit) {
                $builder->add('Inscription', SubmitType::class, [
                    'attr' => [
                        'class' => 'bg-svg hover:bg-hoversvg text-black font-bold py-2 px-4 rounded',
                    ],
                ]);
            }
        }
        
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'is_user' => false,
        ]);

        $resolver->setDefined(['is_edit']);
        $resolver->setDefined(['is_user']);
    }
}
