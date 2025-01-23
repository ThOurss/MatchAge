<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminType;
use App\Form\DeleteUserType;
use App\Service\HashidsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin/dashbaord', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, Security $security): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        return $this->render('admin/index.html.twig', [
            'user' => $user,
            'admin' => $is_admin
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function crudUser(UserRepository $userRepository, Security $security, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        $allusers = $userRepository->findBy(['role' => 7, 'isDelete' => 0]);


        return $this->render('admin/users.html.twig', [
            'user' => $user,
            'admin' => $is_admin,
            'allusers' => $allusers,

        ]);
    }

    #[Route('/admin/moderateurs', name: 'admin_moderateurs')]
    public function crudModerateur(UserRepository $userRepository, Security $security): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        $allmoderateurs = $userRepository->findBy(['role' => 8, 'isDelete' => 0]);

        return $this->render('admin/users.html.twig', [
            'user' => $user,
            'admin' => $is_admin,
            'allusers' => $allmoderateurs,
        ]);
    }

    #[Route('/admin/user/{hash}/change-role', name: 'admin_change_role')]
    public function changeRole(
        string                 $hash,
        HashidsService         $hashidsService,
        Request                $request,
        EntityManagerInterface $entityManager,
        Security               $security,
        UserRepository         $userRepository

    ): Response
    {
        $user = $security->getUser();
        $is_admin = $security->isGranted('ROLE_ADMIN');
        $idUser = $hashidsService->decode($hash);
        $userModif = $userRepository->findOneBy(['id' => $idUser]);

        $form = $this->createForm(AdminType::class, $userModif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Met à jour le rôle dans la base de données
            $this->addFlash('success', 'Rôle mis à jour avec succès.');

            return $this->redirectToRoute('admin_users'); // Redirige vers la liste des utilisateurs ou une autre page
        }

        return $this->render('admin/change_role.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'admin' => $is_admin,
            'userModif' => $userModif,
        ]);
    }

    #[Route('/admin/user/{hash}/delete', name: 'admin_delete_user')]
    public function deleteUser(string                    $hash, HashidsService $hashidsService,
                               Request                   $request, EntityManagerInterface $entityManager,
                               CsrfTokenManagerInterface $csrfTokenManager, UserRepository $userRepository): Response
    {
        $idUser = $hashidsService->decode($hash);
        $userRemove = $userRepository->findOneBy(['id' => $idUser]);
        $submittedToken = $request->request->get('_token');


        // Vérification du jeton CSRF
        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete' . $idUser, $submittedToken))) {
            $userRemove->setIsDelete(true);
            $entityManager->flush();

            return $this->redirectToRoute('admin_users');
        }
        throw $this->createAccessDeniedException('Action non autorisée.');
    }
}
