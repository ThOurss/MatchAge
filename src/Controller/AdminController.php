<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

class AdminController extends AbstractController
{
    #[Route('/admin/user/{id}/change-role', name: 'admin_change_role')]
    public function changeRole(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(AdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Met à jour le rôle dans la base de données
            $this->addFlash('success', 'Rôle mis à jour avec succès.');

            return $this->redirectToRoute('app_user_index'); // Redirige vers la liste des utilisateurs ou une autre page
        }

        return $this->render('admin/index.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
