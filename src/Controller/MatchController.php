<?php


namespace App\Controller;

use App\Entity\MatchUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MatchController extends AbstractController
{
    #[Route('/search', name: 'search_users')]
    public function search(EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser(); // Récupère l'utilisateur connecté.
        $currentUser->setSearching(true);
        $em->flush();

        // Trouve un autre utilisateur en recherche.
        $matchUser = $em->getRepository(User::class)->findSearchingUser($currentUser->getId());

        if ($matchUser) {
            // Crée une correspondance.
            $match = new MatchUser();
            $match->setUser1($currentUser);
            $match->setUser2($matchUser);

            // Marque les deux utilisateurs comme non en recherche.
            $currentUser->setSearching(false);
            $matchUser->setSearching(false);

            $em->persist($match);
            $em->flush();

            return new JsonResponse(['status' => 'matched', 'matchId' => $match->getId()]);
        }

        return new JsonResponse(['status' => 'waiting']);
    }
}

