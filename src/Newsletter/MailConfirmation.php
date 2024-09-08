<?php

namespace App\Newsletter;

use App\Entity\NewsletterEmail;
use App\Entity\Teacher;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailConfirmation
{
    public function __construct(
        private MailerInterface $mailer,
        private string $adminEmail
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

    public function sendTeacher(User $teacher)
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($teacher->getEmail())
            ->subject('KoreanIsta - Inscription d\'un enseignant')
            ->text('Votre profile a bien été enregistré à notre base de donnée. Vous pouvez maintenant vous connectez en tant qu\'enseignant')
            ->html('<p>Votre profile a bien été enregistré à notre base de donnée. Vous pouvez maintenant vous connectez en tant qu\'enseignant</p>');

        $this->mailer->send($email);

    }
}




