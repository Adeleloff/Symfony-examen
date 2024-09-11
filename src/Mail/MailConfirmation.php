<?php

namespace App\Mail;

use App\Entity\NewsletterEmail;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailConfirmation
{
    public function __construct(
        private MailerInterface $mailer,
        private string $adminEmail,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function sendNewsletter(NewsletterEmail $newsletterEmail)
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($newsletterEmail->getEmail())
            ->subject('KoreanIsta - Inscription à la newsletter')
            ->text('Votre email a bien été enregistré à notre newsletter. Vous recevrez bientôt les dernières nouvelles !')
            ->html('<p>Votre email a bien été enregistré à notre newsletter. Vous recevrez bientôt les dernières nouvelles !</p>');

        $this->mailer->send($email);
    }

    public function sendNewsTeacher(User $teacher)
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($teacher->getEmail())
            ->subject('KoreanIsta - Inscription d\'un enseignant')
            ->text('Votre profile a bien été enregistré à notre base de donnée. Vous pouvez maintenant vous connectez en tant qu\'enseignant')
            ->html('<p>Votre profile a bien été enregistré à notre base de donnée. Vous pouvez maintenant vous connectez en tant qu\'enseignant</p>');

        $this->mailer->send($email);

    }

    public function sendPasswordReset(User $user, string $resetToken)
    {
        // Générer l'URL complète pour la réinitialisation du mot de passe
        $resetUrl = $this->urlGenerator->generate('reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from($this->adminEmail)
            ->to($user->getEmail())
            ->subject('KoreanIsta - Réinitialisation de mot de passe')
            ->html(sprintf(
                '<p>Bonjour,</p><p>Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : 
                <a href="%s">Réinitialiser mon mot de passe</a></p>',
                $resetUrl
            ));

        $this->mailer->send($email);
    }

    public function sendPasswordChanged(User $user)
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($user->getEmail())
            ->subject('KoreanIsta - Confirmation de changement de mot de passe')
            ->html('<p>Votre mot de passe a bien été modifié. Si ce changement n\'a pas été initié par vous, veuillez nous contacter immédiatement.</p>');

        $this->mailer->send($email);
    }
}




