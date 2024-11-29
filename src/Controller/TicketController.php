<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketStatusHistory;
use App\Entity\User;
use App\Form\Ticket\TicketType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/ticket')]
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

    #[Route('/', name: 'app_ticket')]
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

    #[Route('/create', name: 'app_ticket_create')]
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

            $this->addFlash('success', 'Ticket created.');

            return $this->redirectToRoute('app_ticket_list');

        }

        return $this->render('ticket/create.html.twig', [
            'submit_message' => 'Create ticket',
            'create_ticket_form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ticket_edit')]
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

            $this->addFlash('success', 'Ticket has been edited.');

            // Redirection
            return $this->redirectToRoute('app_ticket_list');
        }

        return $this->render('ticket/edit.html.twig', [
            'submit_message' => 'Update ticket',
            'edit_ticket_form' => $form->createView(),
        ]);
    }




    #[Route('/tickets/list' , name:'app_ticket_list')]
    public function ticketList(Request $request, TicketRepository $repository): Response
    {
        $tickets = [];

        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $tickets = $repository->findAll();
        } elseif ($this->isGranted('ROLE_TECH_SUPPORT')) {
            $tickets = $repository->findBy(['assigned_to' => $user]);
        } elseif ($this->isGranted('ROLE_USER')) {
            $tickets = $repository->findBy(['created_by' => $user]);
        }

        return $this->render('ticket/list.html.twig', ['tickets' => $tickets]);

    }

    #[Route('/tickets/assigned_to_me' , name:'app_assigned_ticket_list')]
    public function ticket_assigned(Request $request, TicketRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_TECH_SUPPORT');
        $user = $this->getUser();
        $tickets = $repository->findBy(['assigned_to' => $user]);

        return $this->render('ticket/assigned.html.twig', ['tickets' => $tickets]);

    }


    #[Route('/ticket/{id}/delete', name:"app_delete_ticket")]
    public function deleteTicket(Ticket $ticket, TicketRepository $repository){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->entityManager->remove($ticket);
        $this->entityManager->flush();

        $this->addFlash('success', 'Ticket successfully deleted.');
        return $this->redirectToRoute('app_ticket_list');

    }

}
