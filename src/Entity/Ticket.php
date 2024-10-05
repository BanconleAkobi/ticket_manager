<?php

namespace App\Entity;

use App\Enum\Status;
use App\Enum\Priority;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\Column(type: 'string', enumType: Priority::class)]
    private ?Priority $priority = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?User $assigned_to = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    /**
     * @var Collection<int, TicketStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: TicketStatusHistory::class, mappedBy: 'ticker_id')]
    private Collection $ticketStatusHistories;

    public function __construct()
    {
        $this->ticketStatusHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    public function setPriority(Priority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assigned_to;
    }

    public function setAssignedTo(?User $assigned_to): static
    {
        $this->assigned_to = $assigned_to;

        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, TicketStatusHistory>
     */
    public function getTicketStatusHistories(): Collection
    {
        return $this->ticketStatusHistories;
    }

    public function addTicketStatusHistory(TicketStatusHistory $ticketStatusHistory): static
    {
        if (!$this->ticketStatusHistories->contains($ticketStatusHistory)) {
            $this->ticketStatusHistories->add($ticketStatusHistory);
            $ticketStatusHistory->setTickerId($this);
        }

        return $this;
    }

    public function removeTicketStatusHistory(TicketStatusHistory $ticketStatusHistory): static
    {
        if ($this->ticketStatusHistories->removeElement($ticketStatusHistory)) {
            // set the owning side to null (unless already changed)
            if ($ticketStatusHistory->getTickerId() === $this) {
                $ticketStatusHistory->setTickerId(null);
            }
        }

        return $this;
    }
}
