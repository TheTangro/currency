<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Exception\CouldNotProcessException;
use App\Service\Telegram\Commands\CommandInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use TelegramBot\Api\Types\Message;

class CombineProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(
        private readonly iterable $processors
    ) {
    }

    public function isSupported(string $phrase): bool
    {
        foreach ($this->processors as $processor) {
            if ($processor->isSupported($phrase)) {
                return true;
            }
        }

        return false;
    }

    public function process(string $phrase, Message $message): CommandInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->isSupported($phrase)) {
                return $processor->process($phrase, $message);
            }
        }

        throw new CouldNotProcessException($phrase);
    }
}