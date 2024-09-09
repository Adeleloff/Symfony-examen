<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/lesson')]
class LessonController extends AbstractController
{
    #[Route('/', name: 'lesson_index')]
    public function list(LessonRepository $lessonRepository): Response
    {
        // $lessons = $lessonRepository->findAll();
        // trier par date de création (plus récent ou plus vieux)
        $lessons = $lessonRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('lesson/list.html.twig', [
            'lessons' => $lessons,
        ]);
    }

    #[Route('/new', name: 'lesson_new', methods: ['GET', 'POST'])]
    public function newLesson(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $lesson->setTitle($form->get('title')->getData());
            $lesson->setContent($form->get('content')->getData());
            $lesson->setVisible($form->get('visible')->getData());
            $lesson->setTeacher($form->get('teacher')->getData());
            $lesson->setSubCategory($form->get('subCategory')->getData());
            $lesson->setCreatedAt(new \DateTime());

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $lessonVideo */
            $lessonVideo = $form->get('lessonVideo')->getData();

            if ($lessonVideo){
                // si il y a une vidéo alors on déclenche l'upload
                $originalFilename = pathinfo($lessonVideo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $filename = $safeFilename . '-' . uniqid() . '.' . $lessonVideo->guessExtension();

                try {
                    $lessonVideo->move(
                        'uploads/lesson/',
                        $filename
                    );
                    $lesson->setVideoFilename($filename);
                } catch (FileException $e) {
                    $form->addError(new FormError("Erreur lors de l'upload du fichier"));
                }
            }
            
            $entityManager->persist($lesson);
            $entityManager->flush();
            $this->addFlash('success', 'Merci ! 🎉🎉 Votre cours a bien été enregistré');

            return $this->redirectToRoute('home_page');
        }

        return $this->render('lesson/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/edit/{id}', name: 'lesson_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('lesson_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'lesson_delete', methods: ['POST'])]
    public function delete(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lesson);
            $entityManager->flush();
        }

        return $this->redirectToRoute('lesson_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'lesson_item')]
    public function item(Lesson $lesson): Response
    {
        return $this->render('lesson/item.html.twig', [
            'lesson' => $lesson,
        ]);
    }


    // Exemple: d'accés personnalisé pour certains contexts (affiche juste une page avec écrit statistiques)
    /*
    #[Route('/stats', name:"lesson_stats")]
    public function lessonsStats(): Response
    {
        if ($this->isGranted("ROLE_USER")){
            //Si utilisateur alors afficher autre chose que la page de login
        }
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return new Response('statistiques'); // Si pas admin alors redirige sur pas de login
    }
    */
}
