<?php
/// src/Controller/LoginController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
    {
        // Récupérer l'erreur de login si elle existe
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Récupérer le dernier email utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
        $user = $this->getUser();
        if ($user) {
            // Vérifiez si l'utilisateur est bien une instance de l'entité User avant d'utiliser getId()
            if ($user instanceof \App\Entity\User) {
                // Sauvegarder l'ID de l'utilisateur dans la session
                $session->set('user_id', $user->getId());
            }
            return $this->redirectToRoute('app_home'); // Redirection vers la page d'accueil
        }

        // Rendre le fichier twig situé dans le dossier "login"
        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony gère automatiquement la déconnexion.
    }
}
