<?php
// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    public function someAction(SessionInterface $session): Response
    {
        // Récupérer l'ID utilisateur de la session
        $userId = $session->get('user_id');
        
        if ($userId) {
            // Si l'ID utilisateur existe, faire quelque chose avec
            // Par exemple, afficher l'ID dans une vue
            return $this->render('user/some_view.html.twig', [
                'userId' => $userId,
            ]);
        } else {
            // Si l'ID utilisateur n'est pas trouvé, rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }
    }
}

