<?php
// src/Controller/EventController.php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Category;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;




class EventController extends AbstractController
{
    #[Route('/event/create', name: 'app_event_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $event = new Event();
        $categories = $categoryRepository->findAll();
        
        $form = $this->createForm(EventType::class, $event, [
            'categories' => $categories,
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour créer un événement.');
                return $this->redirectToRoute('app_event_create');
            }

            $event->setCreator($user);
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement créé avec succès !');
            return $this->redirectToRoute('app_event_list');
        }

        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    

    /**
     * Route pour la liste des événements.
     *
     * @Route("/events", name="app_event_list", methods={"GET"})
     */
    public function list(Request $request, EventRepository $eventRepository): Response
{
    // Récupérer le terme de recherche global
    $searchTerm = $request->query->get('search');

    // Appliquer le filtre global à la requête
    $queryBuilder = $eventRepository->createQueryBuilder('e');

    if ($searchTerm) {
        $queryBuilder->andWhere('e.title LIKE :searchTerm OR e.location LIKE :searchTerm OR e.description LIKE :searchTerm')
                     ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    // Pagination
    $page = $request->query->getInt('page', 1); // Page actuelle, par défaut 1
    $limit = 6; // Nombre d'événements par page
    $offset = ($page - 1) * $limit; // Calcul de l'offset

    // Appliquer la paginationsea
    $queryBuilder->setMaxResults($limit) // Limiter le nombre de résultats
                 ->setFirstResult($offset); // Définir l'offset

    // Exécuter la requête pour obtenir les événements
    $events = $queryBuilder->getQuery()->getResult();

    // Compter le nombre total d'événements pour la pagination
    $totalEvents = $eventRepository->count([]);
    $totalPages = ceil($totalEvents / $limit); // Calcul du nombre total de pages

    // Si la requête est faite par AJAX, retourner les résultats en JSON
    if ($request->isXmlHttpRequest()) {
        $eventData = [];
        foreach ($events as $event) {
            $eventData[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'date' => $event->getDate()->format('Y-m-d H:i'),
                'location' => $event->getLocation(),
                'category' => $event->getCategory()->getName(),
                'maxParticipants' => $event->getMaxParticipants(),
            ];
        }

        return new JsonResponse(['events' => $eventData]);
    }

    // Rendu de la liste des événements avec le filtre et la pagination pour une requête normale
    return $this->render('event/list.html.twig', [
        'events' => $events,
        'totalPages' => $totalPages,
        'currentPage' => $page, // Passer la page actuelle à la vue
        'searchTerm' => $searchTerm, // Passer le terme de recherche à la vue
    ]);
}

    


    /**
     * Route pour afficher un événement spécifique.
     *
     * @Route("/event/{id}", name="app_event_show", methods={"GET"})
     */
    public function show(Event $event): Response
    {
        // Rendu de la page de détails d'un événement
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Route pour supprimer un événement.
     *
     * @Route("/event/{id}/delete", name="app_event_delete", methods={"POST"})
     */
    public function delete(
        Event $event, 
        EntityManagerInterface $entityManager,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $token = new CsrfToken('delete' . $event->getId(), $request->request->get('_token'));
        
        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
    
        if (!$this->isGranted('ROLE_ADMIN') && $event->getCreator() !== $this->getUser()) {
            throw new AccessDeniedException('Vous n\'avez pas la permission de supprimer cet événement.');
        }
    
        $entityManager->remove($event);
        $entityManager->flush();
    
        $this->addFlash('success', 'Événement supprimé avec succès !');
    
        return $this->redirectToRoute('app_event_list');
    }
 

 /**
     * @Route("/event/{id}/register", name="app_event_register", methods={"POST"})
     */
    public function register(
        Event $event,
        EntityManagerInterface $entityManager,
        
        Request $request,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $this->validateCsrf('register'.$event->getId(), $request);

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }

        if ($event->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        if ($event->getParticipants()->count() >= $event->getMaxParticipants()) {
            $this->addFlash('error', 'Le nombre maximal de participants a été atteint.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $event->addParticipant($user);
        $entityManager->flush();

        // Envoi de notification au créateur
       

        $this->addFlash('success', 'Inscription réussie !');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
    /**
     * @Route("/event/{id}/unregister", name="app_event_unregister", methods={"POST"})
     */
    public function unregister(
        Event $event,
        EntityManagerInterface $entityManager,
        MailerService $eventMailer,
        Request $request,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $this->validateCsrf('unregister'.$event->getId(), $request);

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous désinscrire.');
        }

        if (!$event->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cet événement.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $event->removeParticipant($user);
        $entityManager->flush();

        // Envoi de notification au créateur
       

        $this->addFlash('success', 'Désinscription confirmée.');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    private function validateCsrf(string $tokenId, Request $request): void
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
    }


    #[Route('/event/{id}/edit', name: 'app_event_edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        // Récupérer les catégories disponibles
        $categories = $categoryRepository->findAll();
    
        // Créer le formulaire avec les données existantes de l'événement et les catégories
        $form = $this->createForm(EventType::class, $event, [
            'categories' => $categories,
        ]);
    
        $form->handleRequest($request);
    
        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            $this->addFlash('success', 'Événement mis à jour avec succès !');
    
            return $this->redirectToRoute('app_event_list');
        }
    
        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

/**
 * @Route("/calendar", name="app_event_calendar")
 */
public function calendar(): Response
{
    return $this->render('event/calendar.html.twig');
}
/**
 * @Route("/api/events", name="app_event_api", methods={"GET"})
 */
public function apiEvents(EventRepository $eventRepository): JsonResponse
{
    $events = $eventRepository->findAll();
    $data = [];

    foreach ($events as $event) {
        $data[] = [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'start' => $event->getDate()->format('Y-m-d\TH:i:s'),
            'url' => $this->generateUrl('app_event_show', ['id' => $event->getId()])
        ];
    }

    return new JsonResponse($data);
}
public function listEvents(Request $request, EventRepository $eventRepository): Response
{
    // Récupérer la page actuelle (défaut à 1 si non fourni)
    $page = $request->query->getInt('page', 1);
    $limit = 6; // Nombre d'événements par page

    // Récupérer les événements pour la page actuelle
    $events = $eventRepository->findBy([], ['date' => 'DESC'], $limit, ($page - 1) * $limit);

    // Compter le nombre total d'événements
    $totalEvents = $eventRepository->count([]);
    $totalPages = ceil($totalEvents / $limit); // Nombre total de pages

    return $this->render('event/list.html.twig', [
        'events' => $events,
        'totalPages' => $totalPages,
        'currentPage' => $page, // Passer la page actuelle à la vue
    ]);
}




}
