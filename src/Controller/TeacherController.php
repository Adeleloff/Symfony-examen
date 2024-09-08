<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Entity\User;
use App\Form\UserType;
use App\Newsletter\MailConfirmation;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

            $mailConfirmation->sendTeacher($user);

            return $this->redirectToRoute('teacher_index');  // Redirection après succès 
        }

        return $this->render('teacher/registration.html.twig', [
            'registrationForm' => $form
        ]);
    }

    #[Route('/edit/{id}', name: 'teacher_edit', methods: ['GET', 'POST'])]
    public function editTeacher(
        Request $request, 
        Teacher $teacher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // Récupérer l'utilisateur associé à ce Teacher
        $user = $teacher->getUser();

        $form = $this->createForm(UserType::class, $user); // création du form liée à User

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

    #[Route('/{id}', name: 'teacher_delete', methods: ['POST'])]
    public function delete(Request $request, Teacher $teacher, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$teacher->getId(), $request->getPayload()->getString('_token'))) {
            $user = $teacher->getUser(); // récupére le User liée à ce Teacher
            $entityManager->remove($teacher);
            $entityManager->remove($user); // supprime le User associée
            $entityManager->flush();
        }

        $this->addFlash('success', 'L\'enseignant a été supprimé avec succès.');

        return $this->redirectToRoute('teacher_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'teacher_item')]
    public function item(Teacher $teacher): Response
    {
        return $this->render('teacher/item.html.twig', [
            'teacher' => $teacher,
        ]);
    }
}
