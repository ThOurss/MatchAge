<?php

namespace App\Controller;

use App\Entity\MatchUser;
use App\Entity\User;
use App\Entity\UserMatch;
use App\Form\SearchType;
use App\Repository\MatchUserRepository;
use App\Repository\UserMatchRepository;
use App\Repository\UserRepository;
use App\Service\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request                $request,
                          EntityManagerInterface $entityManager,
                          MatchService           $matchService): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        $matches = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser(); // Utilisateur connecté
            if (!$user) {
                return $this->redirectToRoute('app_login');
            }
            if (!$user->isSearching()) {
                $user->setSearching(true);
                $user->setSearchComplete(false);
                $entityManager->flush();
            }

            return $this->redirectToRoute('search_progress');


        }

        return $this->render('accueil/index.html.twig', [
            'form' => $form->createView(),
            'match' => $matches,
        ]);
    }

    #[route('/search', name: 'search_progress')]
    public function search(EntityManagerInterface $entityManager, MatchUserRepository $matchUserRepository): Response
    {
        $user = $this->getUser(); // Utilisateur connecté

        if (!$user->isSearching()) {
            if ($user->isSearchComplete()) {
                $match = $matchUserRepository->findLastMatchForUser($user->getId());
                $matchName = $matchUserRepository->findOtherUserInMatch($match->getId(), $user->getId());

                return $this->render('accueil/match_found.html.twig', [
                    'match' => $match,
                    'matchName' => $matchName,
                ]);
            }
            return $this->redirectToRoute('app_accueil');
        }


        // Récupérer les utilisateurs en recherche
        $query = $entityManager->createQuery(
            'SELECT u FROM App\Entity\User u 
            LEFT JOIN App\Entity\MatchUser m1 WITH m1.user1 = u AND m1.user2 = :userId
            LEFT JOIN App\Entity\MatchUser m2 WITH m2.user1 = :userId AND m2.user2 = u
            WHERE u.isSearching = true 
            AND u.id != :userId 
            AND u.age BETWEEN :minAge AND :maxAge
            AND u.age >=18
            AND m1.id IS NULL
            AND m2.id IS NULL'
        )->setParameter('userId', $user->getId()
        )->setParameter('minAge', $user->getAge() - 5
        )->setParameter('maxAge', $user->getAge() + 5);

        $potentialMatches = $query->getResult();

        if (!empty($potentialMatches)) {

            try {

                $match = new MatchUser();
                $match->setUser1($user);
                $match->setUser2($potentialMatches[0]);

                $user->setSearching(false); // Arrêter la recherche pour l'utilisateur
                $potentialMatches[0]->setSearching(false); // Arrêter la recherche pour l'autre utilisateur
                $user->setSearchComplete(true);
                $potentialMatches[0]->setSearchComplete(true);


                $entityManager->persist($match);

                $entityManager->flush();

            } catch (OptimisticLockException $e) {

                // Gérer le conflit (par exemple, relancer la recherche)
                return $this->redirectToRoute('search_progress');
            }
            // Associer avec le premier utilisateur trouvé


            return $this->render('accueil/match_found.html.twig', [
                'match' => $match,
                'matchName' => $potentialMatches[0],
            ]);
        }

        // Si aucun match trouvé, continuer la recherche
        return $this->render('accueil/search_progress.html.twig');
    }

    #[route('/cancel_search', name: 'search_cancel')]
    public function cancelSearch(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user->isSearching()) {
            $user->setSearching(false);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_accueil');
    }

}