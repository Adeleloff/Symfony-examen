<?php

namespace App\Controller;

use App\Entity\NewsletterEmail;
use App\Form\NewsletterType;
use App\Mail\MailConfirmation;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    
    #[Route('/', name: 'home_page')]
    public function home(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

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
