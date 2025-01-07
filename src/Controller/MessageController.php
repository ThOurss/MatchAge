<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\MessageStatut;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(EntityManagerInterface $entityManager, ConversationRepository $conversationRepository): Response
    {
        $user = $this->getUser();
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
            $deuxiemeUtilisateur[] = [
                'conversation' => $conversation,
                'otherUsers' => $otherUsers
            ];
        }


        return $this->render('message/index.html.twig', [
            'conversations' => $deuxiemeUtilisateur,


        ]);
    }

    #[Route('/message/show/{id}', name: 'app_message_show')]
    public function showConversation(Request $request, EntityManagerInterface $entityManager, Conversation $id): Response
    {
        $user = $this->getUser();
        $message = new Message();
        $conversation = $id;
        foreach ($conversation->getUser() as $participant) {
            if ($participant !== $user) {
                $otherUsers = $participant;
            }
        }
        foreach ($conversation->getMessage() as $unMessage) {
            $lesMessages[] = $unMessage;

        }
        $form = $this->createForm(MessageType::class, $message, ['attr' => ['class' => 'form-message', 'id' => 'formMessage']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $message->setStatutMessage($entityManager->getRepository(MessageStatut::class)->findOneBy(['id' => 1]));
            $conversation->addMessage($message);
            $message->setUser($user);
            $entityManager->persist($message);

            $entityManager->flush();

        }


        return $this->render('message/show.html.twig', [
            'otherUser' => $otherUsers,
            'form' => $form->createView(),
            'messages' => $lesMessages,
            'currentUser' => $user,
            'conversation' => $id,
        ]);
    }

    #[route('/message/get/{id}', name: 'app_get_message')]
    public function getMessages(Request $request, EntityManagerInterface $entityManager, ConversationRepository $conversationRepository, Conversation $idConv): JsonResponse
    {
        $user = $this->getUser();

        foreach ($idConv->getMessage() as $unMessage) {
            $lesMessages[] = $unMessage->getContenue();
            $userMessage[] = $unMessage->getUser()->getId();
        }

        return new JsonResponse([$lesMessages, $userMessage, $user->getId()]);
    }
}
