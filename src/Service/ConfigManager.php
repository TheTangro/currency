<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ConfigRepository;

class ConfigManager
{
    public const MAX_MESSAGES = 1000000;

    public function __construct(
        private readonly ConfigRepository $configRepository
    ) {
    }

    public function getMaxMessages(): int
    {
        $config = $this->configRepository->findOneByPath('currency/profile/runner/max_messages');

        return (int) $config?->getValue() ?: self::MAX_MESSAGES;
    }

    public function getGithubSecret(): string
    {
        $config = $this->configRepository->findOneByPath('app/webhook/runner/github_secret');

        return (string) $config?->getValue();
    }
}