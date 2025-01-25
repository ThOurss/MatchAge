<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\MessageStatut;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\HashidsService;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(EntityManagerInterface $entityManager, ConversationRepository $conversationRepository, HashidsService $hashidsService, Security $security): Response
    {
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $conversations = $user->getConversation();

        // Initialise un tableau pour les autres utilisateurs
        $deuxiemeUtilisateur = [];

        // Pour chaque conversation, récupére les autres utilisateurs
        foreach ($conversations as $conversation) {
            $otherUsers = [];

            foreach ($conversation->getUser() as $participant) {
                if ($participant !== $user) {
                    $otherUsers[] = $participant;
                }
            }
            //dernier message de chaque conversation

            $lastMessage = $conversation->getMessage()->last();

            $deuxiemeUtilisateur[] = [
                'conversation' => $conversation,
                'otherUsers' => $otherUsers,
                'lastMessage' => $lastMessage,
                'hash' => $hashidsService->encode($conversation->getId()),

            ];
        }


        return $this->render('message/index.html.twig', [
            'conversations' => $deuxiemeUtilisateur,
            'user' => $user,
            'admin' => $is_admin,

        ]);
    }

    #[Route('/message/show/{hash}', name: 'app_message_show')]
    public function showConversation(Security $security, string $hash, HashidsService $hashidsService, Request $request, EntityManagerInterface $entityManager, ConversationRepository $conversationRepository): Response
    {

        $id = $hashidsService->decode($hash);

        if (!$id) {

            throw $this->createNotFoundException('Invalid user ID.');
        }
        $user = $security->getUser();

        $is_admin = $security->isGranted('ROLE_ADMIN');
        $message = new Message();

        $conversation = $conversationRepository->find($id);
        $lstParticipant = $conversation->getUser()->contains($user);
        if (!$lstParticipant) {
            // Rediriger si l'utilisateur n'est pas dans la conversation

            return $this->redirectToRoute('app_accueil'); // Ou une autre route d'erreur
        }

        $allConversations = $user->getConversation();
        $deuxiemeUtilisateur = [];

        // Pour chaque conversation, récupére les autres utilisateurs
        foreach ($allConversations as $uneConversation) {
            $otherUsers = [];
            if ($uneConversation->getId() != $id) {

                foreach ($uneConversation->getUser() as $participant) {
                    if ($participant !== $user) {
                        $otherUsers[] = $participant;
                    }
                }

                //dernier message de chaque conversation

                $lastMessage = $uneConversation->getMessage()->last();
                $deuxiemeUtilisateur[] = [
                    'conversation' => $uneConversation,
                    'otherUsers' => $otherUsers,
                    'hash' => $hashidsService->encode($uneConversation->getId()),
                    'lastMessage' => $lastMessage,
                ];
            }

        }


        foreach ($conversation->getUser() as $participant) {
            if ($participant !== $user) {
                $otherUsers = $participant;

            }
        }
        if (count($conversation->getMessage()) === 0) {
            $lesMessages = "";
        } else {
            foreach ($conversation->getMessage() as $unMessage) {

                $lesMessages[] = $unMessage;


            }
        }


        $form = $this->createForm(MessageType::class, $message, ['attr' => ['class' => 'form-message', 'id' => 'formMessage']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $message->setStatutMessage($entityManager->getRepository(MessageStatut::class)->findOneBy(['id' => 1]));
            $conversation->addMessage($message);
            $message->setUser($user);
            $entityManager->persist($message);

            $entityManager->flush();
            return $this->redirectToRoute('app_message_show', ['hash' => $hashidsService->encode($id)]);

        }


        return $this->render('message/show.html.twig', [
            'otherUser' => $otherUsers,
            'form' => $form->createView(),
            'messages' => $lesMessages,
            'currentUser' => $user,
            'laConversation' => $hashidsService->encode($id),
            'conversations' => $deuxiemeUtilisateur,
            'user' => $user,
            'admin' => $is_admin,
        ]);
    }

    #[route('/message/get/{hash}', name: 'app_get_message')]
    public function getMessages(string $hash, HashidsService $hashidsService, ConversationRepository $conversationRepository): JsonResponse
    {
        $id = $hashidsService->decode($hash);

        if (!$id) {

            throw $this->createNotFoundException('Invalid conversation ID.');
        }
        $user = $this->getUser();
        $idConv = $conversationRepository->find($id);


        foreach ($idConv->getMessage() as $unMessage) {
            $lesMessages[] = $unMessage->getContenue();
            $userMessage[] = $unMessage->getUser()->getId();
        }
        if (count($idConv->getMessage()) === 0) {
            $lesMessages = "";
            $userMessage = "";
            return new JsonResponse([$lesMessages, $userMessage, $user->getId()]);
        }
        return new JsonResponse([$lesMessages, $userMessage, $user->getId()]);
    }
}
