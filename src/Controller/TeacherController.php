<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TeacherController extends AbstractController
{
    
    #[Route('/newsletter/confirmation', name: "newsletter_confirm")]
    public function newsletterConfirm(): Response
    {
        return $this->render('index/newsletter_confirm.html.twig');
    }

    #[Route('/teacher/registration', name: 'teacher_registration', methods: ['GET', 'POST'])]
    public function registerTeacher(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
        ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ajoute le ROLE_TEACHER à l'utilisateur
            $user->setRoles(['ROLE_TEACHER']);

            // Création de l'entité Teacher associée à l'utilisateur
            $teacher = new Teacher();
            $teacher->setUser($user);
            $teacher->setLastName($form->get('lastName')->getData());
            $teacher->setFirstName($form->get('firstName')->getData());
            $teacher->setDateOfBirth($form->get('dateOfBirth')->getData());
            $teacher->setEnrollmentDate($form->get('enrollmentDate')->getData());
            $teacher->setDescription($form->get('description')->getData());

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $profilePic */
            $profilePic = $form->get('profilePic')->getData();

            if ($profilePic){
                // si il y a une photo alors on déclenche l'upload
                $originalFilename = pathinfo($profilePic->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $filename = $safeFilename . '-' . uniqid() . '.' . $profilePic->guessExtension();

                try {
                    $profilePic->move(
                        'uploads/teacher/',
                        $filename
                    );
                    // Ici, nettoyage si il y a déjà une photo
                    if ($teacher->getProfilePicFilename() !== null) {
                        unlink(__DIR__ . "/../../public/uploads/teacher/" . $teacher->getProfilePicFilename());
                    }
                    $teacher->setProfilePicFilename($filename);
                } catch (FileException $e) {
                    $form->addError(new FormError("Erreur lors de l'upload du fichier"));
                }
            }
            // Persist les deux entités
            $entityManager->persist($teacher);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Merci ! 🎉🎉 Votre inscription a bien été enregistré');

            return $this->redirectToRoute('home_page');  // Redirection après succès 
        }

        return $this->render('index/registration.html.twig', [
            'registrationForm' => $form
        ]);
    }

    #[Route('/teacher/confirmation', name: "registration_confirm")]
    public function registerConfirm(): Response
    {
        return $this->render('index/registration_confirm.html.twig');
    }

    #[Route('/teacher', name: 'teacher_list')]
    public function list(TeacherRepository $teacherRepository): Response
    {
        $teachers = $teacherRepository->findAll();
        return $this->render('teacher/list.html.twig', [
            'teachers' => $teachers,
        ]);
    }

    #[Route('/teacher/{id}', name: 'teacher_item')]
    public function item(Teacher $teacher): Response
    {
        return $this->render('teacher/item.html.twig', [
            'teacher' => $teacher,
        ]);
    }
}
