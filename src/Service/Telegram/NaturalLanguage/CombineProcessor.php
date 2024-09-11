<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Exception\CouldNotProcessException;
use App\Service\Telegram\Commands\CommandInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CombineProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(
        private readonly array $processors = []
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

    public function process(string $phrase): CommandInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->isSupported($phrase)) {
                return $processor->process($phrase);
            }
        }

        throw new CouldNotProcessException($phrase);
    }
}