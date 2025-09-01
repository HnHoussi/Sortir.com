<?php

namespace App\Command;

use App\Repository\SortieRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-sortie-status',
    description: 'Met à jour le statut des sorties en "Ouverte"
    si leur date de publication est passée et met à jour le statut en "Fermée"
    si leur date d\inscription est passée.',
)]
class UpdateSortieStatusCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SortieRepository $sortieRepository;
    private StatusRepository $statusRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository,
        StatusRepository $statusRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
        $this->statusRepository = $statusRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTimeImmutable();

        // 1. Récupère le statut 'Ouverte'
        $openStatus = $this->statusRepository->findOneBy(['status_label' => 'Ouverte']);
        $closedStatus = $this->statusRepository->findOneBy(['status_label' => 'Fermée']);
        $createdStatus = $this->statusRepository->findOneBy(['status_label' => 'Créée']);

        if (!$openStatus || !$closedStatus || !$createdStatus) {
            $io->error('Un des statuts par défaut (Ouverte, Fermée, Créée) n\'a pas été trouvé.');
            return Command::FAILURE;
        }

        // --- Logique pour les sorties "Créée" -> "Ouverte" ---
        $sortiesToOpen = $this->sortieRepository->findSortiesToOpen($now);
        $countOpen = count($sortiesToOpen);

        foreach ($sortiesToOpen as $sortie) {
            $sortie->setStatus($openStatus);
        }

        // --- Logique pour les sorties "Ouverte" -> "Fermée" ---
        $sortiesToClose = $this->sortieRepository->findSortiesToClose($now);
        $countClose = count($sortiesToClose);

        foreach ($sortiesToClose as $sortie) {
            $sortie->setStatus($closedStatus);
        }


        // Enregistrement des modifications en base de données
        $this->entityManager->flush();

        $io->success(sprintf(
            '%d sortie(s) passée(s) en "Ouverte".',
            $countOpen
        ));
        $io->success(sprintf(
            '%d sortie(s) passée(s) en "Fermée".',
            $countClose
        ));

        return Command::SUCCESS;
    }
}
