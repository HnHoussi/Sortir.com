<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendInscriptionMail(User $user, Sortie $sortie): void
    {
        $email = (new Email())
            ->from('ne-pas-repondre@sortir.com')
            ->to($user->getEmail())
            ->subject('Confirmation d\'inscription à une sortie')
            ->html($this->twig->render('emails/register.html.twig', [
                'user' => $user,
                'sortie' => $sortie,
            ]));

        $this->mailer->send($email);
    }

    public function sendUnregisterMail(User $user, Sortie $sortie): void
    {
        $email = (new Email())
            ->from('ne-pas-repondre@sortir.com')
            ->to($user->getEmail())
            ->subject('Confirmation de désistement d\'une sortie')
            ->html($this->twig->render('emails/unregister.html.twig', [
                'user' => $user,
                'sortie' => $sortie,
            ]));

        $this->mailer->send($email);
    }
}
