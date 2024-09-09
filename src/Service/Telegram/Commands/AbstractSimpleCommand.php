<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

abstract class AbstractSimpleCommand implements CommandInterface
{
    public function getParseMode(): ?string
    {
        return null;
    }
}