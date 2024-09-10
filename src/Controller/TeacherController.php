<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Mail\MailConfirmation;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/teacher')]
class TeacherController extends AbstractController
{
    #[Route('/', name: 'teacher_index')]
    public function list(TeacherRepository $teacherRepository): Response
    {
        $teachers = $teacherRepository->findAll();
        return $this->render('teacher/list.html.twig', [
            'teachers' => $teachers,
        ]);
    }

    #[Route('/new', name: 'teacher_new', methods: ['GET', 'POST'])]
    public function registerTeacher(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        MailConfirmation $mailConfirmation
        ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

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

            $mailConfirmation->sendNewsTeacher($user);

            return $this->redirectToRoute('teacher_index');  // Redirection après succès 
        }

        return $this->render('teacher/registration.html.twig', [
            'registrationForm' => $form
        ]);
    }

    #[Route('/profile', name: 'teacher_profile')]
    public function profile(): Response
    {
        /**
        * @var \App\Entity\User $user 
        */
        // obliger de mettre cette annotation si non renvoi une instance de UserInterface et non de User
        $user = $this->getUser();

        // Vérifie si le User est bien un Teacher
        if (!$this->isGranted('ROLE_TEACHER')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette section.');
        }

        return $this->render('teacher/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'teacher_edit', methods: ['GET', 'POST'])]
    public function editTeacher(
        Request $request, 
        Teacher $teacher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // Récupére l'utilisateur associé à ce Teacher
        $user = $teacher->getUser();

        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]); 

        // Préremplir les champs non mappés avec les données de Teacher
        $form->get('lastName')->setData($teacher->getLastName());
        $form->get('firstName')->setData($teacher->getFirstName());
        $form->get('dateOfBirth')->setData($teacher->getDateOfBirth());
        $form->get('enrollmentDate')->setData($teacher->getEnrollmentDate());
        $form->get('description')->setData($teacher->getDescription());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $teacher->setLastName($form->get('lastName')->getData());
            $teacher->setFirstName($form->get('firstName')->getData());
            $teacher->setDateOfBirth($form->get('dateOfBirth')->getData());
            $teacher->setEnrollmentDate($form->get('enrollmentDate')->getData());
            $teacher->setDescription($form->get('description')->getData());

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $profilePic */
            $profilePic = $form->get('profilePic')->getData();

            if ($profilePic) {
                // Upload d'une nouvelle photo de profil, si nécessaire
                $originalFilename = pathinfo($profilePic->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $filename = $safeFilename . '-' . uniqid() . '.' . $profilePic->guessExtension();

                try {
                    $profilePic->move(
                        'uploads/teacher/',
                        $filename
                    );
                    // Nettoyage de l'ancienne photo si elle existe
                    if ($teacher->getProfilePicFilename() !== null) {
                        unlink(__DIR__ . "/../../public/uploads/teacher/" . $teacher->getProfilePicFilename());
                    }
                    $teacher->setProfilePicFilename($filename);
                } catch (FileException $e) {
                    $form->addError(new FormError("Erreur lors de l'upload du fichier"));
                }
            }

            $entityManager->persist($user);
            $entityManager->persist($teacher);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('teacher_index');
        }

        return $this->render('teacher/edit.html.twig', [
            'form' => $form->createView(),
            'teacher' => $teacher,
            'user' => $user
        ]);
    }

    #[Route('/change-password', name: 'teacher_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        MailConfirmation $mailerService
        ): Response
    {
        /**
        * @var \App\Entity\User $user 
        */
        // obliger de mettre cette annotation si non renvoi une instance de UserInterface et non de User
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));
            } else {
                
                $newPassword = $form->get('newPassword')->getData();
                $confirmPassword = $form->get('confirmPassword')->getData();

                if ($newPassword !== $confirmPassword) {
                    $form->get('confirmPassword')->addError(new FormError('Les deux mots de passe ne correspondent pas.'));
                } else {
                    
                    $user->setPassword($newPassword);
                    $entityManager->flush();

                    $this->addFlash('success', 'Le mot de passe a été modifié avec succès.');
                    $mailerService->sendPasswordChanged($user);

                    return $this->redirectToRoute('teacher_profile');
                }
            }
        }

        return $this->render('teacher/change_password.html.twig', [
            'passwordform' => $form,
        ]);
    }


    #[Route('/{id}', name: 'teacher_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager): Response
    {
        /**
         * @var \App\Entity\User $user 
         */
        // obliger de mettre cette annotation si non renvoi une instance de UserInterface et non de User
        $user = $this->getUser();
        $teacher = $user->getTeacher();

        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('teacher_profile');
        }

        // Si il y a une entité Teacher associée à l'utilisateur, la supprime
        if ($teacher) {
            $entityManager->remove($teacher);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte ainsi que toutes les données associées ont été supprimés.');

        return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'teacher_item')]
    public function item(Teacher $teacher): Response
    {
        return $this->render('teacher/item.html.twig', [
            'teacher' => $teacher,
        ]);
    }
}
