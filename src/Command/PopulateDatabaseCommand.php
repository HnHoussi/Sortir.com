<?php
//
//namespace App\Command;
//
//use App\Entity\Campus;
//use App\Entity\City;
//use App\Entity\Inscription;
//use App\Entity\Participant;
//use App\Entity\Place;
//use App\Entity\Sortie;
//use App\Entity\Status;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Component\Console\Attribute\AsCommand;
//use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Style\SymfonyStyle;
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//
//#[AsCommand(
//    name: 'app:populate-database',
//    description: 'Adds sample data to the database with realistic location associations.',
//)]
//class PopulateDatabaseCommand extends Command
//{
//    private EntityManagerInterface $entityManager;
//    private UserPasswordHasherInterface $passwordHasher;
//
//    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
//    {
//        parent::__construct();
//        $this->entityManager = $entityManager;
//        $this->passwordHasher = $passwordHasher;
//    }
//
//    protected function execute(InputInterface $input, OutputInterface $output): int
//    {
//        $io = new SymfonyStyle($input, $output);
//        $io->title('Populating the database with sample data and realistic associations...');
//        $io->progressStart(100);
//
//        // --- 1. Créer les entités de base (Campus, Status, City) ---
//        $campuses = [];
//        $campusNames = ['Nantes', 'Rennes', 'Quimper', 'Niort', 'Saint-Herblain'];
//        foreach ($campusNames as $name) {
//            $campus = new Campus();
//            $campus->setCampusName($name);
//            $this->entityManager->persist($campus);
//            $campuses[] = $campus;
//        }
//
//        $statuses = [];
//        $statusLabels = ['Créée', 'Ouverte', 'Fermée', 'En cours', 'Terminée', 'Annulée'];
//        foreach ($statusLabels as $label) {
//            $status = new Status();
//            $status->setStatusLabel($label);
//            $this->entityManager->persist($status);
//            $statuses[] = $status;
//        }
//
//        $cities = [];
//        $citiesData = [
//            ['name' => 'Nantes', 'postalCode' => '44000'],
//            ['name' => 'Rennes', 'postalCode' => '35000'],
//            ['name' => 'Quimper', 'postalCode' => '29000'],
//        ];
//        foreach ($citiesData as $data) {
//            $city = new City();
//            $city->setCityName($data['name']);
//            $city->setPostalCode($data['postalCode']);
//            $this->entityManager->persist($city);
//            $cities[] = $city;
//        }
//        $this->entityManager->flush();
//        $io->progressAdvance(20);
//
//        // --- 2. Créer les participants (dépend de Campus) ---
//        $participants = [];
//        $firstNames = ['Alice', 'Bob', 'Charles', 'Denise', 'Emma', 'Fabien'];
//        $lastNames = ['Dupont', 'Durand', 'Martin', 'Bernard', 'Robert', 'Petit'];
//        for ($i = 0; $i < 100; $i++) {
//            $participant = new Participant();
//            $firstName = $firstNames[array_rand($firstNames)];
//            $lastName = $lastNames[array_rand($lastNames)];
//            $email = strtolower($firstName . '.' . $lastName . rand(1, 99) . '@campus-eni.fr');
//
//            $participant->setFirstName($firstName);
//            $participant->setLastName($lastName);
//            $participant->setPseudo(substr($firstName, 0, 1) . substr($lastName, 0, 1) . $i);
//            $participant->setEmail($email);
//            $participant->setPhone('0' . rand(6, 7) . rand(10000000, 99999999));
//            // Ligne à remplacer plus tard avec le hasher
//            $participant->setPassword('password');
//            $participant->setAdministrator(false);
//            $participant->setIsActive(true);
//            $participant->setCampus($campuses[array_rand($campuses)]);
//
//            $this->entityManager->persist($participant);
//            $participants[] = $participant;
//        }
//        $this->entityManager->flush();
//        $io->progressAdvance(20);
//
//        // --- 3. Créer les lieux (dépend de City) ---
//        $places = [];
//        $placesData = [
//            'Nantes' => ['Parc de Procé', 'Jardin des Plantes', 'Place Graslin', 'Passage Pommeraye'],
//            'Rennes' => ['Parc du Thabor', 'Place de la Mairie', 'Place des Lices'],
//            'Quimper' => ['Jardin de la Retraite', 'Place de la Résistance', 'Cathédrale Saint-Corentin'],
//        ];
//
//        foreach ($placesData as $cityName => $placeNames) {
//            $city = null;
//            foreach ($cities as $c) {
//                if ($c->getCityName() === $cityName) {
//                    $city = $c;
//                    break;
//                }
//            }
//
//            if ($city) {
//                foreach ($placeNames as $name) {
//                    $place = new Place();
//                    $place->setPlaceName($name);
//                    $place->setStreet(rand(1, 100) . ' Rue de ' . $cityName);
//                    $place->setLatitude(rand(4700, 4800) / 100);
//                    $place->setLongitude(rand(-100, -20) / 100);
//                    $place->setCity($city);
//                    $this->entityManager->persist($place);
//                    $places[] = $place;
//                }
//            }
//        }
//        $this->entityManager->flush();
//        $io->progressAdvance(20);
//
//        // --- 4. Créer les sorties (dépend de Participant, Status, Place) ---
//        $sorties = [];
//        for ($i = 0; $i < 100; $i++) {
//            $sortie = new Sortie();
//            $sortie->setName('Sortie ' . ($i + 1));
//            $sortie->setStartDatetime(new \DateTime(sprintf('+%d days', rand(1, 60))));
//            $sortie->setDuration(rand(30, 300));
//            $sortie->setRegistrationDeadline(new \DateTime(sprintf('+%d days', rand(1, 30))));
//            $sortie->setMaxRegistrations(rand(5, 20));
//            $sortie->setDescription('Description de la sortie numéro ' . ($i + 1));
//            $sortie->setEventState(rand(0, 4));
//
//            // Sélection d'un lieu, puis association de l'organisateur en fonction du campus de la ville du lieu
//            $place = $places[array_rand($places)];
//            $cityOfPlace = $place->getCity();
//
//            // Trouver un participant sur le bon campus pour l'organisateur
//            $organizersInSameCityCampus = array_filter($participants, function($p) use ($cityOfPlace) {
//                return $p->getCampus()->getCampusName() === $cityOfPlace->getCityName();
//            });
//            $organizer = empty($organizersInSameCityCampus) ? $participants[array_rand($participants)] : $organizersInSameCityCampus[array_rand($organizersInSameCityCampus)];
//
//            $sortie->setOrganisateur($organizer);
//            $sortie->setStatus($statuses[array_rand($statuses)]);
//            $sortie->setOrganizer($organizer->getId());
//            $sortie->setPlace($place->getId());
//            $sortie->setState(rand(0, 4));
//
//            $this->entityManager->persist($sortie);
//            $sorties[] = $sortie;
//        }
//        $this->entityManager->flush();
//        $io->progressAdvance(20);
//
//        // --- 5. Créer les inscriptions (dépend de Participant et Sortie) ---
//        for ($i = 0; $i < 100; $i++) {
//            $inscription = new Inscription();
//
//            $inscription->setParticipant($participants[array_rand($participants)]);
//            $inscription->setSortie($sorties[array_rand($sorties)]);
//
//            $this->entityManager->persist($inscription);
//        }
//        $this->entityManager->flush();
//        $io->progressFinish();
//
//        $io->success('Database successfully populated with sample data!');
//
//        return Command::SUCCESS;
//    }
//}
