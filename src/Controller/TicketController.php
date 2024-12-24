<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketStatusHistory;
use App\Entity\User;
use App\Form\Ticket\TicketsFilterType;
use App\Form\Ticket\TicketType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
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


    #[Route('/gestion', name: 'app_gestion_ticket')]
    public function manageTicket(Request $request, TicketRepository $repository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        // Créez le formulaire
        $ticketsFilterForm = $this->createForm(TicketsFilterType::class);
        $ticketsFilterForm->handleRequest($request);

        if ($ticketsFilterForm->isSubmitted()) {
            // Si le bouton "Réinitialiser" est cliqué
            if ($ticketsFilterForm->get('reset')->isClicked()) {
                $request->getSession()->remove('ticket_filters'); // Supprimez les filtres de la session
                return $this->redirectToRoute('app_gestion_ticket'); // Rechargez la page
            }

            // Si le formulaire de filtre est soumis
            if ($ticketsFilterForm->isValid()) {
                $filters = [
                    'start' => $ticketsFilterForm->get('StartDate')->getData(),
                    'end' => $ticketsFilterForm->get('EndDate')->getData(),
                    'status' => $ticketsFilterForm->get('status')->getData(),
                ];

                $query = $repository->findByFiltersQuery($filters, $user, $user->getRoles());

                $tickets = $paginator->paginate(
                    $query,
                    $request->query->getInt('page', 1),
                    5
                );

                //ajout du filtre à la session car probleme de redirection.
                $request->getSession()->set('ticket_filters', $filters);
                return $this->redirectToRoute('app_gestion_ticket', [
                    'page' => $request->query->getInt('page', 1),
                ]);            }
        }

        $filters = $request->getSession()->get('ticket_filters', []);

        $query = $repository->findByFiltersQuery($filters, $user, $user->getRoles());

        // Recherchez les tickets par défaut
        $tickets = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('ticket/managed.html.twig', [
            'tickets' => $tickets,
            'ticketFilterForm' => $ticketsFilterForm->createView(),
        ]);
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
