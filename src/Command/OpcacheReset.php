<?php

namespace App\Command;

use App\Service\PoisonPillManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cache:opcache:clear',
    description: 'Opcache reset',
)]
class OpcacheReset extends Command
{
    public function __construct(
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

       if (function_exists('opcache_reset')) {
           opcache_reset();
       }

        return Command::SUCCESS;
    }
}
