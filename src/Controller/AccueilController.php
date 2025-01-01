<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\MatchAccept;
use App\Entity\MatchUser;
use App\Entity\User;
use App\Entity\UserMatch;
use App\Form\deleteMatchType;
use App\Form\MatchType;
use App\Form\SearchType;
use App\Repository\MatchUserRepository;
use App\Repository\UserMatchRepository;
use App\Repository\UserRepository;
use App\Service\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Tests\ORM\Functional\Ticket\MyEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

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
            'message' => '',
            'match' => $matches,
        ]);
    }

    #[route('/search', name: 'search_progress')]
    public function search(Request $request, EntityManagerInterface $entityManager, MatchUserRepository $matchUserRepository): Response
    {
        $user = $this->getUser(); // Utilisateur connecté

        if (!$user->isSearching()) {
            if ($user->isSearchComplete()) {
                $match = $matchUserRepository->findLastMatchForUser($user->getId());
                $matchName = $matchUserRepository->findOtherUserInMatch($match->getId(), $user->getId());
                return $this->redirectToRoute('match_found', ['id' => $matchName->getId()]);

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
                $user1Accept = $entityManager->getRepository(MatchAccept::class)->findOneBy(['id' => 1]);
                $user2Accept = $entityManager->getRepository(MatchAccept::class)->findOneBy(['id' => 1]);
                $match = new MatchUser();
                $match->setUser1($user);
                $match->setMatchAccepted1($user1Accept);
                $match->setUser2($potentialMatches[0]);
                $match->setMatchAccepted2($user2Accept);

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

            return $this->redirectToRoute('match_found', ['id' => $potentialMatches[0]->getId()]);
            
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

    #[route('/match_find/{id}', name: 'match_found')]
    public function matchFound(User $id, Request $request, EntityManagerInterface $entityManager, MatchUserRepository $matchUserRepository): Response
    {
        $userMatch = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(MatchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $match = $matchUserRepository->findMatchWithIdUser($user->getId(), $userMatch->getId());


            if ($form->get('RefuserMatch')->isClicked()) {
                $userRefuse = $entityManager->getRepository(MatchAccept::class)->findOneBy(['id' => 3]);

                if ($match->getUser1()->getId() == $user->getId()) {
                    $match->setMatchAccepted1($userRefuse);
                } else {
                    $match->setMatchAccepted2($userRefuse);
                }

                $entityManager->persist($match);

                $entityManager->flush();

                if ($match->getVersion() != 3) {
                    return $this->redirectToRoute('app_accueil');
                } else {

                    unset($match);
                    return $this->redirectToRoute('app_accueil');
                }


            } else {
                $userAccepte = $entityManager->getRepository(MatchAccept::class)->findOneBy(['id' => 2]);

                if ($match->getUser1()->getId() == $user->getId()) {
                    $match->setMatchAccepted1($userAccepte);
                    $statut = $match->setMatchAccepted1($userAccepte);

                } else {
                    $match->setMatchAccepted2($userAccepte);
                    $statut = $match->setMatchAccepted2($userAccepte);

                }

                $entityManager->persist($match);

                $entityManager->flush();
                if ($match->getVersion() != 3) {
                    return $this->redirectToRoute('match_accept', ['id' => $userMatch->getId()]);

                } else {
                    if ($match->getMatchAccepted1()->getId() == 3 or $match->getMatchAccepted2()->getId() == 3) {
                        $entityManager->remove($match);
                        $entityManager->flush();
                        unset($match);
                        return $this->redirectToRoute('match_accept', ['id' => $userMatch->getId()]);

                    } elseif ($match->getMatchAccepted1()->getId() == 2 and $match->getMatchAccepted2()->getId() == 2) {

                        return $this->redirectToRoute('match_accept', ['id' => $userMatch->getId()]);

                    }


                }

            }


        }
        return $this->render('accueil/match_found.html.twig', [
            'form' => $form->createView(),
            'matchName' => $userMatch,

        ]);
    }

    #[Route('/match_accept/{id}', name: 'match_accept')]
    public function matchAcceptWait(User $id, EntityManagerInterface $entityManager, MatchUserRepository $matchUserRepository, CsrfTokenManagerInterface $csrfTokenManager): Response
    {

        $user = $this->getUser();
        $match = $matchUserRepository->findLastMatchForUser($user->getId());
        $userMatch = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        $csrfToken = $csrfTokenManager->getToken('delete_matchUser')->getValue();
        if ($match->getUser1()->getId() == $user->getId()) {
            $statut = $match->getMatchAccepted2();
        } else {
            $statut = $match->getMatchAccepted1();
        }
        return $this->render('accueil/match_accept_wait.html.twig', [
            'csrf_token' => $csrfToken,
            'matchName' => $userMatch,
            'statut' => $statut,
            'matchUser' => $match,
        ]);
    }

    #[Route('/match_delete/{id}', name: 'delete_matchUser', methods: ['POST'])]
    public function deleteMatch(Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, MatchUserRepository $matchUserRepository): Response
    {
        $user = $this->getUser();
        $submittedToken = $request->request->get('_token');
        $match = $matchUserRepository->findLastMatchForUser($user->getId());
        // Vérification du jeton CSRF
        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete' . $match->getId(), $submittedToken))) {
            $entityManager->remove($match);
            $entityManager->flush();

            return $this->redirectToRoute('app_accueil');
        }
        throw $this->createAccessDeniedException('Action non autorisée.');


    }

}