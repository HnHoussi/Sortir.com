<?php
// src/Command/PopulateDbSqlCommand.php

namespace App\Command;

use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Place;
use App\Entity\Status;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-db-sql',
    description: 'Populates the database with SQL and adds Sortie entities coherently.',
)]
class PopulateDbSqlCommand extends Command
{
    private Connection $connection;
    private EntityManagerInterface $em;

    public function __construct(Connection $connection, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->connection = $connection;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Populating database...');

        // --- 1. Execute raw SQL file ---
        $sqlPath = __DIR__ . '/../../sql/data/database_population.sql';

        if (file_exists($sqlPath)) {
            try {
                $sqlContent = file_get_contents($sqlPath);
                $this->connection->executeQuery($sqlContent);
                $io->success('Database populated via SQL script!');
            } catch (\Exception $e) {
                $io->error(sprintf('SQL execution failed: %s', $e->getMessage()));
            }
        } else {
            $io->warning(sprintf('SQL file not found: %s (skipping)', $sqlPath));
        }

        // --- 2. Insert Sortie entities ---
        try {
            // Ensure related entities exist
            $organisator = $this->em->getRepository(User::class)->find(1);
            $place = $this->em->getRepository(Place::class)->find(1);
            $status = $this->em->getRepository(Status::class)->find(1);

            if (!$organisator || !$place || !$status) {
                $io->warning('Missing User, Place, or Status (id=1). Cannot insert Sortie.');
                return Command::SUCCESS;
            }

            // Create a few sorties
            for ($i = 1; $i <= 3; $i++) {
                $sortie = new Sortie();
                $sortie->setName("Sortie Test $i")
                    ->setStartDatetime(new \DateTime("+$i days"))
                    ->setDuration(120)
                    ->setRegistrationDeadline(new \DateTime("+$i days -1 day"))
                    ->setMaxRegistrations(15)
                    ->setDescription("Sortie de test numéro $i")
                    ->setPhotoUrl(null)
                    ->setPlace($place)
                    ->setState(1) // example: 1 = open
                    ->setStatus($status)
                    ->setOrganisator($organisator);

                $this->em->persist($sortie);
            }

            $this->em->flush();
            $io->success('Sortie entities inserted successfully!');
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to insert Sortie entities: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
