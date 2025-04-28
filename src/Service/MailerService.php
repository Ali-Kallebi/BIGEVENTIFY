<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNotification(
        string $creatorEmail,
        string $participantName,
        string $eventTitle,
        string $action,
        \DateTimeInterface $eventDate,
        string $eventUrl
    ): void {
        $subject = ($action === 'register') 
            ? "Nouvelle inscription à votre événement" 
            : "Désinscription de votre événement";

        $email = (new TemplatedEmail())
            ->from(new Address('alikallebi92@gmail.com', 'Plateforme Événements'))
            ->to($creatorEmail)
            ->subject($subject)
            ->htmlTemplate('emails/event_notification.html.twig')
            ->context([
                'participantName' => $participantName,
                'eventTitle' => $eventTitle,
                'action' => $action,
                'actionText' => ($action === 'register') ? 'inscrit' : 'désinscrit',
                'eventDate' => $eventDate,
                'eventUrl' => $eventUrl
            ]);

        $this->mailer->send($email);
    }
}