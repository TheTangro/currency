<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

abstract class AbstractHtmlCommand implements CommandInterface
{
    public function getParseMode(): ?string
    {
        return 'HTML';
    }
}