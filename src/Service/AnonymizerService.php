<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AnonymizerService
{
    public function anonymize(User $user): void
    {
        // Mark inactive
        $user->setIsActive(false);

        // Anonymize personal data
        $user->setFirstName('Anonyme');
        $user->setLastName('');
        $user->setEmail('anonyme-' . $user->getId() . '@example.com'); // unique fake email
        $user->setPhone(null);
        $user->setPseudo('Anonyme-' . $user->getId());

        // Remove user from future sorties where they were registered
        foreach ($user->getSortiesInscrit() as $sortie) {
            $user->removeSortiesInscrit($sortie);
            $sortie->removeUser($user);
        }
    }

}
