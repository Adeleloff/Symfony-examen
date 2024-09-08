<?php

namespace App\Controller;

use App\Entity\NewsletterEmail;
use App\Entity\Teacher;
use App\Entity\User;
use App\Form\NewsletterType;
use App\Form\RegistrationFormType;
use App\Newsletter\MailConfirmation;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class IndexController extends AbstractController
{
    
    #[Route('/', name: 'home_page')]
    public function home(CategoryRepository $categoryRepository): Response
    {
        // 1 - Je requête le modèle (SQL/BDD)
        // pour récupérer les catégories
        $categories = $categoryRepository->findAll();

        // 2 - Je demande à Twig de rendre une vue
        // et je lui passe les catégories
        // Répertoire racine des vues : templates/
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/about', name: 'about_page')]
    public function about(): Response
    {
        return $this->render('index/about.html.twig', [
        ]);
    }

    #[Route('/newsletter/subscribe', name: "newsletter_subscribe", methods: ['GET', 'POST'])]
    public function newsletterSubscribe(
        Request $request,
        EntityManagerInterface $em,
        MailConfirmation $mailConfirmation
    ): Response {
        $newsletter = new NewsletterEmail();
        $form = $this->createForm(NewsletterType::class, $newsletter);

        // Prend en charge la requête entrante
        // et s'il y a des données, les met dans $newsletter
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($newsletter);
            $em->flush();
            $this->addFlash('success', 'Merci ! 🎉🎉 Votre email a été enregistré ');

            $mailConfirmation->sendNewsletter($newsletter);

            return $this->redirectToRoute('home_page');
        }

        return $this->render('index/newsletter.html.twig', [
            'newsletterForm' => $form
        ]);
    }
}
