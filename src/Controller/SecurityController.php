<?php

namespace App\Controller;

use App\Form\ForgottenPasswordType;
use App\Form\ResetPasswordType;
use App\Mail\MailConfirmation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion de votre pare-feu.');
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(
        Request $request, 
        UserRepository $userRepository, 
        MailConfirmation $mailerService, 
        TokenGeneratorInterface $tokenGenerator, 
        EntityManagerInterface $entityManager
        ): Response
    {
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            if (!$user) {
                $form->addError(new FormError('Aucun compte associé à cette adresse email.'));
            } else {
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->flush();

                $mailerService->sendPasswordReset($user, $token);

                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe.');
                return $this->redirectToRoute('login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(
        Request $request, 
        string $token, 
        UserRepository $userRepository, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        MailConfirmation $mailerService
        ): Response
    {
        $user = $userRepository->findOneByResetToken($token);

        if (!$user) {
            $this->addFlash('danger', 'Le lien est invalide.');
            return $this->redirectToRoute('forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $user->setResetToken(null);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été réinitialisé.');
            $mailerService->sendPasswordChanged($user);
            return $this->redirectToRoute('login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
            'token' => $token,
        ]);
    }
}
