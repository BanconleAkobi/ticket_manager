<?php

namespace App\Controller\UserBundle\registration;

use App\Controller\Exceptions\MailException;
use App\Entity\User;
use App\Form\User\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();


            // mail sending

            $mail =(new Email())
                ->subject("Inscription à ticks")
                ->from("ingenieurbanconle@gmail.com")
                ->to($user->getEmail())
                ->html("Vous venez de vous inscrire sur notre site de <b>gestion de tickets</b>. <br> Nous vous en remercions <br> Nous espérons que vous puissiez trouver votre bonheur.<br> L'équipe Ticks");

                try{
                    $mailer->send($mail);
                    $this->addFlash("success", "Votre compte a bien été enrégistrez, veuillez vous connecter");
                }catch(\Exception $e){
                    throw new MailException("Erreur lors de la confirmation du mail: " . $e->getMessage());
                }




            return $this->redirectToRoute('app_login');
        }

        return $this->render('UserBundle/registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
