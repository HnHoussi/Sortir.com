<?php
// src/Command/PopulateDbSqlCommand.php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-db-sql',
    description: 'Populates the database using raw SQL scripts.',
)]
class PopulateDbSqlCommand extends Command
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Executing SQL scripts to populate the database...');

        $sqlPath = 'sql/data/database_population.sql'; // Le chemin vers votre fichier SQL

        if (!file_exists($sqlPath)) {
            $io->error(sprintf('SQL file not found: %s', $sqlPath));
            return Command::FAILURE;
        }

        try {
            $sqlContent = file_get_contents($sqlPath);
            $this->connection->executeQuery($sqlContent);
            $io->success('Database successfully populated with SQL scripts!');
        } catch (\Exception $e) {
            $io->error(sprintf('An error occurred: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
