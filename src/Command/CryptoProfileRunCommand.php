<?php

namespace App\Command;

use App\Entity\ParserProfile;
use App\Kernel;
use App\Repository\ParserProfileRepository;
use App\Service\ConfigManager;
use App\Service\PoisonPillManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'crypto:profile:run',
    description: 'Add a short description for your command',
)]
class CryptoProfileRunCommand extends Command
{
    public const ERRORS_BEFORE_FAILING = 5;

    /**
     * @var Process[]
     */
    private array $processPool = [];

    private array $processErrors = [];

    private array $terminatedProcesses = [];

    public function __construct(
        private readonly ParserProfileRepository $parserProfileRepository,
        private readonly Kernel $kernel,
        private readonly LoggerInterface $logger,
        private readonly PoisonPillManager $poisonPillManager,
        private readonly ConfigManager $configManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        do {
            $profiles = $this->parserProfileRepository->findAll();

            if (empty($profiles)) {
                return Command::SUCCESS;
            }

            foreach ($profiles as $profile) {
                if ($this->isProfileShouldRan($profile)) {
                    $this->runProfile($profile);
                }
            }

            sleep(1);
        } while (!$this->poisonPillManager->isNeedDie());

        return Command::SUCCESS;
    }

    public function runProfile(ParserProfile $parserProfile): void
    {
        $process = $this->processPool[$parserProfile->getHash()] ?? null;

        if (!$process || $this->canRestart($process, $parserProfile)) {
            $newProcess = new Process(
                command: $this->buildCommand($parserProfile),
                cwd: $this->kernel->getProjectDir(),
                timeout: null
            );

            $this->processPool[$parserProfile->getHash()] = $newProcess;
            $newProcess->start();
        }
    }

    private function buildCommand(ParserProfile $parserProfile): array
    {
        $command = [];
        $command[] = 'php';
        $command[] = 'bin/console';
        $command[] = 'crypto:currency:parse';
        $command[] = sprintf('--from=%s', $parserProfile->getCurrencyFrom());
        $command[] = sprintf('--to=%s', $parserProfile->getCurrencyTo());

        if ($this->configManager->getMaxMessages()) {
            $command[] = sprintf('--max-messages=%s', (string) $this->configManager->getMaxMessages());
        }

        return $command;
    }

    private function canRestart(Process $process, ParserProfile $parserProfile): bool
    {
        if ($process->getExitCode() !== Command::SUCCESS) {
            $this->logger->error(
                sprintf(
                    'Process hypervisor info: process for profile id=%d has been terminated with error. Error: %s',
                    $parserProfile->getId(),
                    $process->getErrorOutput()
                )
            );

            $errorCount = $this->processErrors[$parserProfile->getHash()] ?? 0;
            $this->processErrors[$parserProfile->getHash()] = ++$errorCount;

            if ($this->processErrors[$parserProfile->getHash()] > self::ERRORS_BEFORE_FAILING) {
                $this->terminatedProcesses[] = $parserProfile;

                return false;
            }
        }

        return true;
    }

    public function isProfileShouldRan(ParserProfile $parserProfile): bool
    {
        $process = $this->processPool[$parserProfile->getHash()] ?? null;

        return $process === null
            || (!$process->isRunning()
            && !in_array($parserProfile->getHash(), $this->terminatedProcesses, true));
    }
}
