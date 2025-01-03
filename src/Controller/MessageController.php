<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(EntityManagerInterface $entityManager, ConversationRepository $conversationRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $conversations = $user->getConversation();

        // Initialise un tableau pour les autres utilisateurs
        $deuxiemeUtilisateur = [];

        // Pour chaque conversation, récupére les autres utilisateurs
        foreach ($conversations as $conversation) {
            $otherUsers = [];
            foreach ($conversation->getUser() as $participant) {
                if ($participant !== $user) {
                    $otherUsers[] = $participant;
                }
            }
            $deuxiemeUtilisateur[] = [
                'conversation' => $conversation,
                'otherUsers' => $otherUsers
            ];
        }


        return $this->render('message/index.html.twig', [
            'conversations' => $deuxiemeUtilisateur,


        ]);
    }
}
