<?php

namespace App\Entity;

use App\Repository\ParserProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParserProfileRepository::class)]
class ParserProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'parser_profile_id', initialValue: 1)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $currency_from = null;

    #[ORM\Column(length: 10)]
    private ?string $currency_to = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCurrencyFrom(): ?string
    {
        return $this->currency_from;
    }

    public function setCurrencyFrom(string $currency_from): static
    {
        $this->currency_from = $currency_from;

        return $this;
    }

    public function getCurrencyTo(): ?string
    {
        return $this->currency_to;
    }

    public function setCurrencyTo(string $currency_to): static
    {
        $this->currency_to = $currency_to;

        return $this;
    }

    public function getHash(): string
    {
        return sha1($this->getCurrencyFrom() . $this->getCurrencyTo());
    }
}
