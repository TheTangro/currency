<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Service\Telegram\Commands\CommandInterface;

class SimpleProfitNotification implements ProcessorInterface
{
    public function isSupported(string $phrase): bool
    {
        // TODO: Implement isSupported() method.
    }

    public function process(string $phrase): CommandInterface
    {
        // TODO: Implement process() method.
    }
}