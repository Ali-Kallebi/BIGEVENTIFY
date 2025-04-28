<?php
namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_event_calendar')]
    public function index(): Response
    {
        return $this->render('event/calendar.html.twig');
    }

    #[Route('/api/events', name: 'app_event_api')]
    public function eventsJson(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();
        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getDate()->format('Y-m-d\TH:i:s'),
            ];
        }

        return new JsonResponse($data);
    }
}
