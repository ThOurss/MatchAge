<?php


namespace App\Controller;

use App\Entity\MatchUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatchController extends AbstractController
{
    #[Route('/search', name: 'start_search', methods: ['POST'])]
    public function startSearch(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser(); // Utilisateur connecté.

        $user->setSearching(true);
        $em->flush();

        return new JsonResponse(['status' => 'search_started']);
    }

    #[Route('/find-match', name: 'find_match', methods: ['GET'])]
    public function findMatch(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur est en recherche
        $currentUser = $em->getRepository(User::class)->find($user->getId());
        if (!$currentUser || !$currentUser->isSearching()) {
            return new JsonResponse(['status' => 'not_searching']);
        }

        // Rechercher un autre utilisateur
        $match = $em->getRepository(User::class)->findSearchingUser($currentUser->getId());

        if ($match) {
            // Mettre à jour le statut des deux utilisateurs
            $currentUser->setSearching(false);
            $match->setSearching(false);

            // Enregistrer le match pour les deux utilisateurs
            $currentUser->setMatchedUser($match);
            $match->setMatchedUser($currentUser);

            $em->flush();

            return new JsonResponse([
                'status' => 'match_found',
                'user' => [
                    'id' => $match->getId(),
                    'firstname' => $match->getFirstName(),
                ],
            ]);
        }

        return new JsonResponse(['status' => 'no_match']);
    }

}

