<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\LoginType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        $user = $security->getUser();
        $is_admin = $security->isGranted('ROLE_ADMIN');
        $is_moderateur = $security->isGranted('ROLE_MODERATOR');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'admin' => $is_admin,
            'user' => $user,
            'moderateur' => $is_moderateur
        ]);
    }


    #[Route('/profile', name: 'user_profile')]
    public function profile(#[CurrentUser] ?User $user): Response
    {
        if (!$user) {
            return $this->json(['message' => 'Not logged in'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Security $security): Response
    {
        // logout the user in on the current firewall
        $response = $security->logout();

        // you can also disable the csrf logout
        $response = $security->logout(false);

        return $this->redirectToRoute('app_accueil');
        // ... return $response (if set) or e.g. redirect to the homepage
    }
}