<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Exception\CouldNotProcessException;
use App\Service\Telegram\Commands\CommandInterface;
use TelegramBot\Api\Types\Message;

interface ProcessorInterface
{
    public function isSupported(string $phrase): bool;

    /**
     * @param string $phrase
     *
     * @return CommandInterface
     *
     * @throws CouldNotProcessException
     */
    public function process(string $phrase, Message $message): ?CommandInterface;
}