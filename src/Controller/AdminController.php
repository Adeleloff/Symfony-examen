<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Teacher;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin')]
/**
* @IsGranted({"ROLE_ADMIN"}) 
*/
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les leçons
        $lessons = $entityManager->getRepository(Lesson::class)->findAll();

        // Récupérer toutes les catégories
        $categories = $entityManager->getRepository(Category::class)->findAll();

        // Récupérer toutes les sous-catégories
        $subcategories = $entityManager->getRepository(SubCategory::class)->findAll();

        // Récupérer tous les enseignants
        $teachers = $entityManager->getRepository(Teacher::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'lessons' => $lessons,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'teachers' => $teachers
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_user' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ajoute le ROLE_ADMIN à l'utilisateur
            $user->setRoles(['ROLE_ADMIN']);

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Merci ! 🎉🎉 Un nouvel administrateur a bien été enregistré');

            return $this->redirectToRoute('admin_dashboard');  // Redirection après succès 
        }

        return $this->render('admin/new.html.twig', [
            'form' => $form
        ]);
    }
}
