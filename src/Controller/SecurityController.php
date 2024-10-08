<?php

namespace App\Controller;

use App\Entity\PasswordResetRequest;
use App\Form\ForgottenPasswordType;
use App\Form\ResetPasswordType;
use App\Mail\MailConfirmation;
use App\Repository\PasswordResetRequestRepository;
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
        $error = $authenticationUtils->getLastAuthenticationError();

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
    ): Response {
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            if ($user) {
                $token = $tokenGenerator->generateToken();
                
                // Créer une nouvelle demande de réinitialisation
                $passwordResetRequest = new PasswordResetRequest();
                $passwordResetRequest->setUser($user);
                $passwordResetRequest->setToken($token);
                $passwordResetRequest->setExpiresAt(new \DateTime('+1 hour'));

                $entityManager->persist($passwordResetRequest);
                $entityManager->flush();

                $mailerService->sendPasswordReset($user, $token);

                return $this->render('security/forgot_password_confirmation.html.twig');
            } else {
                $this->addFlash('danger', 'Aucun utilisateur trouvé avec cet email.');
            }

            return $this->redirectToRoute('login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'requestForm' => $form,
        ]);
    }



    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(
        Request $request, 
        string $token,  
        EntityManagerInterface $entityManager,
        MailConfirmation $mailerService,
        PasswordResetRequestRepository $passwordResetRequestRepository
    ): Response {
        // Chercher la demande de réinitialisation par le token
        $passwordResetRequest = $passwordResetRequestRepository->findOneBy(['token' => $token]);

        if (!$passwordResetRequest || $passwordResetRequest->getExpiresAt() < new \DateTime()) {
            $this->addFlash('danger', 'Le lien est invalide ou a expiré.');
            return $this->render('security/invalid_token.html.twig');
        }

        $user = $passwordResetRequest->getUser();

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($newPassword !== $confirmPassword) {
                $form->get('confirmPassword')->addError(new FormError('Les deux mots de passe ne correspondent pas.'));
            } else {
            
                $user->setPassword($newPassword);
                $entityManager->persist($user);

                $entityManager->remove($passwordResetRequest);
                $entityManager->flush();

                $mailerService->sendPasswordChanged($user);

                $this->addFlash('success', 'Votre mot de passe a bien été réinitialisé.');
                return $this->redirectToRoute('login');

            }
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
            'token' => $token,
        ]);
    }

}
