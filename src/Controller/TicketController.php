<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketStatusHistory;
use App\Entity\User;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class TicketController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private TicketRepository $ticketRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, TicketRepository $ticketRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->ticketRepository = $ticketRepository;
    }

    #[Route('/ticket', name: 'app_ticket')]
    public function index(Request $request): Response
    {
        $filters = (count($request->request->all('filters')) != 0) ? $request->request->all('filters') : [];
        $orderBy = (count($request->request->all('orderBy')) != 0) ? $request->request->all('orderBy') : ['created_at' => 'DESC'];

        /** @var User */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $tickets = $this->ticketRepository->findBy($filters, $orderBy);

        } else {
            $userFilter = $filters['created_by'] ?? null;
            $filters['created_by'] = $user;

            $tickets = $this->ticketRepository->findBy($filters, $orderBy);

            if ($this->isGranted('ROLE_TECH_SUPPORT')) {
                if ($userFilter) {
                    $filters['created_by'] = $userFilter;
                }

                $filters['assigned_by'] = $user;

                $tickets += $this->ticketRepository->findBy($filters, $orderBy);
            }
        }

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/ticket/create/', name: 'app_ticket_create')]
    public function createTicket(Request $request): Response
    {
    
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket, [
            'tech_support_list' => $this->userRepository->findByRole('ROLE_TECH_SUPPORT'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User */
            $user = $this->getUser();

            $ticket->setCreatedAt(new \DateTimeImmutable());
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $ticket->setCreatedBy($user);
            $this->entityManager->persist($ticket);
            $this->entityManager->flush();
            
            $user->addTicket($ticket);

            return $this->redirectToRoute('app_ticket');
        }

        return $this->render('ticket/create.html.twig', [
            'submit_message' => 'Create ticket',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ticket/{id}/edit/', name: 'app_ticket_edit')]
    public function editTicket(Ticket $ticket, Request $request): Response
    {
        $ticketPreviousStatus = $ticket->getStatus();
        $form = $this->createForm(TicketType::class, $ticket, [
            'tech_support_list' => $this->userRepository->findByRole('ROLE_TECH_SUPPORT'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the status of the ticket
            $this->entityManager->flush();

            if ($ticketPreviousStatus !== $form->get('status')->getData()) {
                // Create a new entry in the ticket_status_history table
                $ticketStatusHistory = new TicketStatusHistory();
                $ticketStatusHistory->setTicketId($ticket);
                $ticketStatusHistory->setStatus($form->get('status')->getData());
                $ticketStatusHistory->setChangedBy($this->getUser());
                $ticketStatusHistory->setChangedAt(new \DateTimeImmutable());

                $this->entityManager->persist($ticketStatusHistory);
                $this->entityManager->flush();

                /** @var User */
                $user = $this->getUser();
                $user->addTicketStatusHistory($ticketStatusHistory);
            }

            // Redirection
            return $this->redirectToRoute('app_ticket');
        }

        return $this->render('ticket/edit.html.twig', [
            'submit_message' => 'Update ticket',
            'form' => $form->createView(),
        ]);
    }
}
