<?php

namespace App\Controller;

use App\Form\ModerateurType;
use App\Repository\UserRepository;
use App\Service\HashidsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ModerateurController extends AbstractController
{
    #[Route('/moderateur/dashboard', name: 'moderateur_dashboard')]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, Security $security): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        $is_moderateur = $security->isGranted('ROLE_MODERATOR');
        return $this->render('moderateur/index.html.twig', [
            'user' => $user,
            'admin' => $is_admin,
            'moderateur' => $is_moderateur
        ]);
    }

    #[Route('/moderateur/users', name: 'moderateur_users')]
    public function crudUser(UserRepository $userRepository, Security $security, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        $is_moderateur = $security->isGranted('ROLE_MODERATOR');
        $allusers = $userRepository->findBy(['role' => 7, 'isDelete' => 0]);


        return $this->render('moderateur/user.html.twig', [
            'user' => $user,
            'admin' => $is_admin,
            'moderateur' => $is_moderateur,
            'allusers' => $allusers,

        ]);
    }

    #[Route('/moderateur/user/{hash}/change-role', name: 'moderateur_change_role')]
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
        $is_moderateur = $security->isGranted('ROLE_MODERATOR');
        $idUser = $hashidsService->decode($hash);
        $userModif = $userRepository->findOneBy(['id' => $idUser]);

        $form = $this->createForm(ModerateurType::class, $userModif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Met à jour le rôle dans la base de données
            $this->addFlash('success', 'Rôle mis à jour avec succès.');

            return $this->redirectToRoute('moderateur_users'); // Redirige vers la liste des utilisateurs ou une autre page
        }

        return $this->render('moderateur/change_role.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'admin' => $is_admin,
            'moderateur' => $is_moderateur,
            'userModif' => $userModif,
        ]);
    }

    #[Route('/moderateur/user/{hash}/delete', name: 'moderateur_delete_user')]
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

            return $this->redirectToRoute('mderateur_users');
        }
        throw $this->createAccessDeniedException('Action non autorisée.');
    }
}
