<?php

namespace App\Entity;

use App\Repository\ApartmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApartmentRepository::class)]
class Apartment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'ultimaReserva', cascade: ['persist', 'remove'])]
    private ?Reservation $reservatiob = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getReservatiob(): ?Reservation
    {
        return $this->reservatiob;
    }

    public function setReservatiob(Reservation $reservatiob): static
    {
        // set the owning side of the relation if necessary
        if ($reservatiob->getUltimaReserva() !== $this) {
            $reservatiob->setUltimaReserva($this);
        }

        $this->reservatiob = $reservatiob;

        return $this;
    }
}
