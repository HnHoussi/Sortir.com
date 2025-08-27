<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/profil')]
final class ParticipantController extends AbstractController
{
    #[Route('/', name: '_list')]
    public function _list(ParticipantRepository $participantRepository): Response
    {
        //methode pour afficher la liste de tous les participants

        $participants = $participantRepository->findAll();

        return $this->render('/participant/list.html.twig', [
            'participants' => $participants,
        ]);
    }


    #[Route('/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, ParticipantRepository $participantRepository): Response
    {
        //methode pour afficher le detail d'un participant
        $participant = $participantRepository->find($id);
        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé');
        }
        return $this->render('/participant/detail.html.twig', [
            'participant' => $participant,
        ]);
    }
}
