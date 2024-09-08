<?php

namespace App\Command;

use App\Service\PoisonPillManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:poison-pill:renew',
    description: 'Add a short description for your command',
)]
class PosionPillRenewCommand extends Command
{
    public function __construct(
        private readonly PoisonPillManager $poisonPillManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->poisonPillManager->regeneratePoison();

        return Command::SUCCESS;
    }
}
