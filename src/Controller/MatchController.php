<?php


namespace App\Controller;

use App\Entity\MatchUser;
use App\Entity\User;
use App\Entity\UserMatch;
use App\Service\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatchController extends AbstractController
{
    /*#[Route('/search', name: 'start_search', methods: ['POST'])]
    public function startSearch(EntityManagerInterface $em,Security $security): JsonResponse
    {
        $currentUser = $security->getUser();
        if (!$currentUser){
            return new JsonResponse(['status' => 'no_connect']);
        }
        $user = $this->getUser(); // Utilisateur connecté.

        $user->setSearching(true);

        $em->flush();

        return new JsonResponse(['status' => 'search_started']);

    }*/

    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(Request $request,Security $security, MatchService $matchService): JsonResponse
    {
        $currentUser = $security->getUser();
        if (!$currentUser){
            return new JsonResponse(['status' => 'no_connect']);
        }
        $match = $matchService->findMatch($currentUser);

        if ($match) {
            return new JsonResponse([
                'success' => true,
                'match' => [
                    'id' => $match->getId(),
                    'name' => $match->getName(),
                ],
            ]);
        }

        return new JsonResponse(['success' => false]);
    }

    #[Route('/find-match', name: 'find_match', methods: ['GET'])]
    public function findMatch(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur est encore en recherche dans la base de données.
        $currentUser = $em->getRepository(User::class)->find($user->getId());
        if (!$currentUser || !$currentUser->isSearching()) {
            return new JsonResponse(['status' => 'not_searching']);
        }

        // Rechercher un utilisateur disponible et éviter de se sélectionner soi-même.
        $potentielMatch = $em->getRepository(User::class)->findSearchingUser($currentUser->getId());

        if ($potentielMatch) {
            // Met à jour le statut des deux utilisateurs.


            // Enregistrer le match pour les deux utilisateurs
            $match=new UserMatch();
            $em->persist($match);
            $matchUser1 = new MatchUser($currentUser, $match);
            $matchUser2 = new MatchUser($potentielMatch, $match);

            $em->persist($matchUser1);
            $em->persist($matchUser2);

            $currentUser->setSearching(false);
            $potentielMatch->setSearching(false);


            $em->flush();

            return new JsonResponse([
                'status' => 'match_found',
                'user' => [
                    'id' => $potentielMatch->getId(),
                    'firstname' => $potentielMatch->getFirstName(),
                ],
            ]);
        }

        return new JsonResponse(['status' => 'no_match']);
    }
    #[Route('/check-match', name: 'check_match', methods: ['GET'])]
    public function checkMatch(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $currentUser = $em->getRepository(User::class)->find($user->getId());

        if ($currentUser->getMatchedUser()) {
            $matchedUser = $currentUser->getMatchedUser();

            return new JsonResponse([
                'status' => 'match_found',
                'user' => [
                    'id' => $matchedUser->getId(),
                    'firstname' => $matchedUser->getFirstName(),
                ],
            ]);
        }

        return new JsonResponse(['status' => 'no_match']);
    }

}

