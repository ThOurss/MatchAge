<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
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
