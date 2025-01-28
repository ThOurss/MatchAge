<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\MatchAccept;
use App\Entity\MatchUser;
use App\Entity\User;
use App\Form\MatchType;
use App\Form\SearchType;
use App\Repository\MatchUserRepository;
use App\Repository\UserRepository;
use App\Service\HashidsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
                          Security               $security): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');

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
            'admin' => $is_admin,
            'user' => $user,
        ]);
    }

    #[route('/search', name: 'search_progress')]
    public function search(HashidsService $hashidsService, Security $security, Request $request, EntityManagerInterface $entityManager, MatchUserRepository $matchUserRepository): Response
    {
        $user = $security->getUser();
        $is_admin = $security->isGranted('ROLE_ADMIN'); // Utilisateur connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isSearching()) {
            if ($user->isSearchComplete()) {
                $match = $matchUserRepository->findLastMatchForUser($user->getId());
                $matchName = $matchUserRepository->findOtherUserInMatch($match->getId(), $user->getId());
                return $this->redirectToRoute('match_found', ['hash' => $hashidsService->encode($matchName->getId())]);

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

            return $this->redirectToRoute('match_found', ['hash' => $hashidsService->encode($potentialMatches[0]->getId())]);

        }

        // Si aucun match trouvé, continuer la recherche
        return $this->render('accueil/search_progress.html.twig', [
            'user' => $user,
            'admin' => $is_admin,
        ]);
    }

    #[route('/cancel_search', name: 'search_cancel')]
    public function cancelSearch(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if ($user->isSearching()) {
            $user->setSearching(false);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_accueil');
    }

    #[route('/match_find/{hash}', name: 'match_found')]
    public function matchFound(string $hash, HashidsService $hashidsService, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, MatchUserRepository $matchUserRepository, Security $security): Response
    {
        $user = $security->getUser();
        $is_admin = $security->isGranted('ROLE_ADMIN');
        $idUser = $hashidsService->decode($hash);
        $userMatch = $userRepository->findOneBy(['id' => $idUser]);

        $form = $this->createForm(MatchType::class);
        $form->handleRequest($request);

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $match = $matchUserRepository->findMatchWithIdUser($user->getId(), $userMatch->getId());
        if (!$match) {
            return $this->redirectToRoute('app_accueil');
        }
        if ($form->isSubmitted() && $form->isValid()) {


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
                    return $this->redirectToRoute('match_accept', ['hash' => $hashidsService->encode($userMatch->getId())]);

                } else {
                    if ($match->getMatchAccepted1()->getId() == 3 or $match->getMatchAccepted2()->getId() == 3) {

                        return $this->redirectToRoute('match_accept', ['hash' => $hashidsService->encode($userMatch->getId())]);

                    } elseif ($match->getMatchAccepted1()->getId() == 2 and $match->getMatchAccepted2()->getId() == 2) {
                        $conv = new conversation();
                        $conv->addUser($user);
                        $conv->addUser($userMatch);
                        $entityManager->persist($conv);
                        $entityManager->flush();

                        return $this->redirectToRoute('match_accept', ['hash' => $hashidsService->encode($userMatch->getId())]);

                    }


                }

            }


        }
        return $this->render('accueil/match_found.html.twig', [
            'form' => $form->createView(),
            'matchName' => $userMatch,
            'user' => $user,
            'admin' => $is_admin,
        ]);
    }

    #[Route('/match_accept/{hash}', name: 'match_accept')]
    public function matchAcceptWait(string $hash, HashidsService $hashidsService, EntityManagerInterface $entityManager, MatchUserRepository $matchUserRepository, CsrfTokenManagerInterface $csrfTokenManager, Security $security): Response
    {

        $user = $security->getUser();
        $is_admin = $security->isGranted('ROLE_ADMIN');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $idDecode = $hashidsService->decode($hash);
        $userMatch = $entityManager->getRepository(User::class)->findOneBy(['id' => $idDecode]);
        $match = $matchUserRepository->findMatchWithIdUser($user->getId(), $userMatch->getId());
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
            'user' => $user,
            'admin' => $is_admin,
        ]);
    }

    #[Route('/match_delete/{id}', name: 'delete_matchUser', methods: ['POST'])]
    public function deleteMatch(Security $security, MatchUser $match, Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, MatchUserRepository $matchUserRepository): Response
    {
        $user = $security->getUser();
        $is_admin = $security->isGranted('ROLE_ADMIN');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $submittedToken = $request->request->get('_token');


        // Vérification du jeton CSRF
        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete' . $match->getId(), $submittedToken))) {
            $entityManager->remove($match);
            $entityManager->flush();

            return $this->redirectToRoute('app_accueil');
        }
        throw $this->createAccessDeniedException('Action non autorisée.');


    }

}