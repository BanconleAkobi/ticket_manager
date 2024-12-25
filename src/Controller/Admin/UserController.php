<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\User\UserType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin/user')]
class UserController extends AbstractController
{

    private EntityManagerInterface $em;
    private RoleRepository $roleRepository;

    public function __construct(EntityManagerInterface $em, RoleRepository $roleRepository){
        $this->em = $em;
        $this->roleRepository = $roleRepository;
    }

    #[Route('/list', name: 'app_admin_user_list')]
    public function index(Request $request, UserRepository $repository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $query = $repository->findAll();

        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('admin/user_list/index.html.twig', [
            'users' => $users,
            'page' => $request->query->getInt('page', 1)
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_edit_user')]
    public function editUser(User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $appRoles = $this->roleRepository->getAllRoles();
        $userRoles = $user->getRoles();

        $form = $this->createForm(UserType::class, $user, [
            'appRoles' => $appRoles,
            'userRoles' => $userRoles
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateUserRoles($form, $user, $appRoles);
            $this->em->flush();
            
            $this->addFlash('success', 'User has been edited.');

            return $this->redirectToRoute('app_admin_user_list');
        }

        return $this->render('admin/user_list/edit.html.twig', [
            'submit_message' => 'Update user',
            'edit_user_form' => $form->createView(),
        ]);
    }

    private function updateUserRoles($form, User $user, array $appRoles): void
    {
        $roles = [];
        foreach ($appRoles as $role) {
            if ($form->get($role)->getData()) {
                in_array($role, $roles) ?: $roles[] = $role;
                foreach($this->roleRepository->getRolesBelow($role) as $roleBis) {
                    in_array($roleBis, $roles) ?: $roles[] = $roleBis;
                }
            }
        }
        $user->setRoles($roles);
    }

    #[Route('/{id}/delete', name:"app_delete_user")]
    public function deleteUser(User $user) : Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'User successfully deleted.');
        return $this->redirectToRoute('app_admin_user_list');

    }
}
