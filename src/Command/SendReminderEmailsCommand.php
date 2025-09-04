<?php

namespace App\Command;

use App\Repository\SortieRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'send:reminder-emails',
    description: 'Add a short description for your command',
)]
class SendReminderEmailsCommand extends Command
{

    protected static $defaultName = 'app:send-reminder-emails';
    protected static $defaultDescription = 'Envoie des e-mails de rappel pour les sorties prévues dans 48 heures.';
    private $sortieRepository;
    private $mailer;

    public function __construct(SortieRepository $sortieRepository, MailerInterface $mailer)
    {
        parent::__construct();
        $this->sortieRepository = $sortieRepository;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        $sortiesToRemind = $this->sortieRepository->findUpcomingSorties();

        if (empty($sortiesToRemind)) {
            $io->info('Aucune sortie à rappeler aujourd\'hui.');
            return Command::SUCCESS;
        }

        foreach ($sortiesToRemind as $sortie) {
            foreach ($sortie->getUsers() as $user) {
                $email = (new Email())
                    ->from('no-reply@sortir.com')
                    ->to($user->getEmail())
                    ->subject('Rappel de sortie : ' . $sortie->getName())
                    ->text('Bonjour ' . $user->getPseudo() . ', n\'oubliez pas que la sortie "' . $sortie->getName() . '" a lieu dans 48 heures !')
                    ->html('<p>Bonjour ' . $user->getPseudo() . ',<br><br>N\'oubliez pas que la sortie **' . $sortie->getName() . '** a lieu dans 48 heures !</p>');

                $this->mailer->send($email);
            }
        }

        $io->success('E-mails de rappel envoyés avec succès.');

        return Command::SUCCESS;
    }
}
