<?php

// src/MessageHandler/SortieReminderHandler.php

namespace App\MessageHandler;

use App\Message\SortieReminder;
use App\Repository\SortieRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SortieReminderHandler
{
    private $sortieRepository;
    private $mailer;

    public function __construct(SortieRepository $sortieRepository, MailerInterface $mailer)
    {
        $this->sortieRepository = $sortieRepository;
        $this->mailer = $mailer;
    }

    public function __invoke(SortieReminder $message)
    {
        $sortiesToRemind = $this->sortieRepository->findUpcomingSorties();

        if (empty($sortiesToRemind)) {
            // Vous pouvez ajouter une logique de log ici si nécessaire
            return;
        }

        foreach ($sortiesToRemind as $sortie) {
            foreach ($sortie->getUsers() as $user) {
                $email = (new Email())
                    ->from('no-reply@sortir.com')
                    ->to($user->getEmail())
                    ->subject('Rappel de sortie : ' . $sortie->getName())
                    ->html('<p>Bonjour ' . $user->getPseudo() . ',<br><br>N\'oubliez pas que la sortie **' . $sortie->getName() . '** a lieu dans 48 heures !</p>');

                $this->mailer->send($email);
            }
        }
    }
}
