<?php


namespace App\Controller;

use App\Entity\MatchUser;
use App\Entity\User;
use App\Entity\UserMatch;
use App\Form\SearchType;
use App\Repository\UserRepository;
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

    #[Route('/search', name: 'user_searchhhh', methods: ['GET', 'POST'])]
    public function search(
        Request $request,
        EntityManagerInterface $entityManager,
        MatchService $matchService
    ): Response {

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        $matches=[];
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user) {
                return $this->redirectToRoute('app_login');
            }
            $user->setSearching(true);
            $entityManager->flush();

            // Trouver un match
            $matches = $matchService->findMatch($user);

            if ($matches) {
                // Désactiver `isSearching` pour les deux utilisateurs
                $user->setSearching(false);
                $matches->setSearching(false);
                $entityManager->flush();

                return $this->render('accueil/index.html.twig', [
                    'match' => $matches,
                ]);
            }

            return $this->render('accueil/index.html.twig', [
                'match' => $matches,
                'message' => 'Aucun utilisateur trouvé.',
            ]);
        }
dump($matches);
        return $this->render('accueil/index.html.twig', [
            'form' => $form->createView(),
            'match' => $matches,
        ]);
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

