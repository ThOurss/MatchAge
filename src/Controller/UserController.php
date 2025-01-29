<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\HashidsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Config\Security\PasswordHasherConfig;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        $is_moderateur = $security->isGranted('ROLE_MODERATOR');
        $role = $entityManager->getRepository(Role::class)->findOneBy(['id' => 7]);


        $userNew = new User();

        $form = $this->createForm(UserType::class, $userNew);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userNew->setRole($role);

            $plainPassword = $form->get('password')->getData();

            // Hachez le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($userNew, $plainPassword);
            $userNew->setPassword($hashedPassword);

            $entityManager->persist($userNew);

            $entityManager->flush();

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'admin' => $is_admin,
            'form' => $form,
            'moderateur' => $is_moderateur,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user, EntityManagerInterface $entityManager, Security $security): Response
    {
        $userRole = $security->getUser();
        if (!$userRole) {
            return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'userRole' => $userRole,

        ]);
    }

    #[Route('/{hash}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $entityManager, Security $security, HashidsService $hashidsService): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $is_admin = $security->isGranted('ROLE_ADMIN');
        $is_moderateur = $security->isGranted('ROLE_MODERATOR');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('inscriptionUser')->isClicked()) {
                $plainPassword = $form->get('password')->getData();

                // Hachez le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_edit', ['hash' => $hashidsService->encode($user->getId())], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_user_delete', ['hash' => $hashidsService->encode($user->getId())], Response::HTTP_SEE_OTHER);
            }

        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'admin' => $is_admin,
            'moderateur' => $is_moderateur,
        ]);
    }

    #[Route('/delete/{hash}', name: 'app_user_delete')]
    public function delete(string $hash, Request $request, EntityManagerInterface $entityManager, HashidsService $hashidsService, Security $security, UserRepository $userRepository): Response
    {
        $idUser = $hashidsService->decode($hash);
        $userRemove = $userRepository->findOneBy(['id' => $idUser]);

        $userRemove->setIsDelete(true);
        $entityManager->flush();

        return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
    }


}
