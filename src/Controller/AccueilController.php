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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request,
                          EntityManagerInterface $entityManager,
                          MatchService $matchService): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        $matches=[];
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();


            if (!$user) {
                return $this->redirectToRoute('app_login');
            }
            try{
                $matches=$matchService->searchForMatch($user);
                if($matches){
                    return $this->redirectToRoute('user_search',[
                        'id' => $matches->getId(),
                    ]);
                }
                return $this->render('accueil/index.html.twig', [
                    'form' => $form->createView(),
                    'match' => $matches,
                ]);
            }catch (\Exception $e) {
                return $this->render('accueil/index.html.twig', [
                    'form' => $form->createView(),
                    'match' => $matches,
                ]);
            }









        }

        return $this->render('accueil/index.html.twig', [
            'form' => $form->createView(),
            'match' => $matches,
        ]);
    }


 #[Route('/search_result/{id}', name: 'user_search', methods: ['GET', 'POST'])]
    public function search(User $user) {



        return $this->render('accueil/show.html.twig',[
            'matchUser'=>$user,
        ]);
    }
    #[Route('/trigger-action', name: 'trigger_action', methods: ['GET', 'POST'])]
    public function triggerAction(): Response
    {
        // Logique de l'action à déclencher
        // Par exemple, envoyer un email, mettre à jour une base de données, etc.
        return $this->json([
            'message' => 'Action déclenchée avec succès !',
        ]);
    }
}