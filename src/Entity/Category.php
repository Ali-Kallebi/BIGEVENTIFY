<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\CategoryRepository;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Event::class, orphanRemoval: true)]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    // Getter et Setter pour ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et Setter pour Name
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    // Getter pour la relation avec les événements
    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    // Ajouter un événement
    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setCategory($this);
        }
        return $this;
    }

    // Supprimer un événement
    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // Assurez-vous que l'événement ne référence plus cette catégorie
            if ($event->getCategory() === $this) {
                $event->setCategory(null);
            }
        }
        return $this;
    }

    // Permet d'afficher le nom de la catégorie dans un formulaire ou un select
    public function __toString(): string
    {
        return $this->name;
    }
}
