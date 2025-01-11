<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

class AdminController extends AbstractController
{
    #[Route('/admin/dashbaord', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, Security $security): Response
    {

        return $this->render('admin/index.html.twig', []);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function crudUser(UserRepository $userRepository, Security $security): Response
    {
        $allusers = $userRepository->findBy(['role' => 7]);

        return $this->render('admin/users.html.twig', [
            'allusers' => $allusers,
        ]);
    }

    #[Route('/admin/moderateurs', name: 'admin_moderateurs')]
    public function crudModerateur(UserRepository $userRepository, Security $security): Response
    {
        $allmoderateurs = $userRepository->findBy(['role' => 8]);

        return $this->render('admin/moderateur.html.twig', [
            'allmoderateurs' => $allmoderateurs,
        ]);
    }

    #[Route('/admin/user/{id}/change-role', name: 'admin_change_role')]
    public function changeRole(
        User                   $user,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(AdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Met à jour le rôle dans la base de données
            $this->addFlash('success', 'Rôle mis à jour avec succès.');

            return $this->redirectToRoute('app_user_index'); // Redirige vers la liste des utilisateurs ou une autre page
        }

        return $this->render('admin/change_role.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
