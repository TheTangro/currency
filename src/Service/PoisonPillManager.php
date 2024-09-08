<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PoisonPill;
use App\Repository\PoisonPillRepository;

class PoisonPillManager
{
    private ?PoisonPill $poisonPill;

    public function __construct(
        private readonly PoisonPillRepository $poisonPillRepository,
    ) {
        $this->poisonPill = null;
    }

    public function regeneratePoison(): PoisonPill
    {
        $poison = new PoisonPill();
        $poison->setPill(hash('sha256', random_bytes(64)));
        $this->poisonPillRepository->save($poison);

        return $poison;
    }

    public function start(): void
    {
        $poisonPill = $this->poisonPillRepository->getOrNull();

        if (!$poisonPill) {
            $this->regeneratePoison();
            $this->start();
        } else {
            if ($this->poisonPill !== null && $this->poisonPill->getPill() !== $poisonPill->getPill()) {
                $this->poisonPill = null;
                $this->regeneratePoison();
                $this->start();
            } elseif ($this->poisonPill === null) {
                $this->poisonPill = $poisonPill;
            }
        }
    }

    public function isNeedDie(): bool
    {
        if ($this->poisonPill === null) {
            $this->start();
        }

        $poisonPill = $this->poisonPillRepository->getOrNull();

        if ($poisonPill) {
            return $this->poisonPill->getPill() !== $poisonPill->getPill();
        }

        return false;
    }
}