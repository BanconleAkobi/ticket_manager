<?php

namespace App\Controller\Admin;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin')]
class UserListController extends AbstractController
{

    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    #[Route('/user/list', name: 'app_admin_user_list')]
    public function index(UserRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $repository->findAll();

        return $this->render('admin/user_list/index.html.twig', [
            'users' => $users,
        ]);
    }



    #[Route('/user/{id}/delete', name:"app_delete_user")]
    public function deleteUser(User $user, TicketRepository $repository){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'User successfully deleted.');
        return $this->redirectToRoute('app_admin_user_list');

    }
}
