<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConfirmDeletingController extends AbstractController
{
    #[Route('/confirm/deleting/{id}/{entity_name}', name: 'app_confirm_deleting')]
    public function index($entity_name, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('confirm_deleting/index.html.twig', [
            'entity_name' => $entity_name,
            'id' => $id,
        ]);
    }
}
