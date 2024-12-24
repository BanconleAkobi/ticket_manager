<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketStatusHistory;
use App\Entity\User;
use App\Form\Ticket\TicketFilterType;
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



    #[Route('/create', name: 'app_ticket_create')]
    public function createTicket(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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

            return $this->redirectToRoute('app_gestion_ticket');

        }

        return $this->render('ticket/create.html.twig', [
            'submit_message' => 'Create ticket',
            'create_ticket_form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ticket_edit')]
    public function editTicket(Ticket $ticket, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
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
            return $this->redirectToRoute('app_gestion_ticket');
        }

        return $this->render('ticket/edit.html.twig', [
            'submit_message' => 'Update ticket',
            'edit_ticket_form' => $form->createView(),
        ]);
    }



    #[Route('/gestion' , name:'app_gestion_ticket')]
    public function manageTicket(Request $request, TicketRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $ticketsFilterForm = $this->createForm(TicketFilterType::class);
        if ($request->isMethod('POST')) {
            $ticketsFilterForm->handleRequest($request);
            if($ticketsFilterForm->isSubmitted() && $ticketsFilterForm->isValid()) {
                $tickets = $repository->findByFilters($ticketsFilterForm->getData(), $user, $user->getRoles());
            }
        }else{
            $tickets = $repository->findByFilters([], $user, $user->getRoles());
        }

        return $this->render('ticket/managed.html.twig', ['tickets' => $tickets, 'ticketFilterForm' => $ticketsFilterForm->createView()]);
    }


    #[Route('/{id}/delete', name:"app_delete_ticket")]
    public function deleteTicket(Ticket $ticket, TicketRepository $repository){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->entityManager->remove($ticket);
        $this->entityManager->flush();

        $this->addFlash('success', 'Ticket successfully deleted.');
        return $this->redirectToRoute('app_gestion_ticket');

    }

}
