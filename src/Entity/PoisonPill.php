<?php

namespace App\Entity;

use App\Repository\PoisonPillRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PoisonPillRepository::class)]
class PoisonPill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pill = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPill(): ?string
    {
        return $this->pill;
    }

    public function setPill(string $pill): static
    {
        $this->pill = $pill;

        return $this;
    }
}
