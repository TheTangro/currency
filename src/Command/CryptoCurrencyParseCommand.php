<?php

namespace App\Command;

use App\Entity\CurrencyRate;
use App\Exception\MaxMessagesExceedException;
use App\Repository\CurrencyRateRepository;
use App\Service\Currency\ManagerInterface;
use App\Service\DataRetrievers\SubscriberInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'crypto:currency:parse',
    description: 'Add a short description for your command',
)]
class CryptoCurrencyParseCommand extends Command
{
    public function __construct(
        private readonly SubscriberInterface $subscriber,
        private readonly ManagerInterface $currencyManager,
        private readonly LoggerInterface $logger,
        private readonly CurrencyRateRepository $currencyRateRepository,
        private readonly KernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('from', '-f', InputOption::VALUE_REQUIRED, 'Currency From')
            ->addOption('to', '-t', InputOption::VALUE_REQUIRED, 'Currency To')
            ->addOption('max-messages', '-m', InputOption::VALUE_OPTIONAL, 'Max Allowed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $from = $input->getOption('from');
        $to = $input->getOption('to');
        $maxMessages = $input->getOption('max-messages');
        $maxMessages = $maxMessages ? (int) $maxMessages : null;

        if (empty($from) || empty($to)) {
            $io->error('From and To currencies is required');

            return Command::FAILURE;
        }

        try {
            $this->subscriber->subscribe(
                $from,
                $to,
                fn(CurrencyRate $currencyRate) => $this->processNewCurrencyRate($currencyRate, $maxMessages)
            );
        } catch (MaxMessagesExceedException) {
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->logger->error($e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param CurrencyRate $currencyRate
     * @param int|null $maxAllowed
     *
     * @return void
     *
     * @throws MaxMessagesExceedException
     */
    private function processNewCurrencyRate(CurrencyRate $currencyRate, ?int $maxAllowed): void
    {
        static $processed = 0;

        if (++$processed >= $maxAllowed && $maxAllowed !== null) {
            exit;
        }

        try {
            $last = $this->currencyRateRepository->getLast(
                $currencyRate->getCurrencyFrom(),
                $currencyRate->getCurrencyTo()
            );

            if (!$last || $last->getRate() !== $currencyRate->getRate()) {
                $this->currencyManager->writeNewCurrencyRateAsObject($currencyRate);

            }
        } catch (\Throwable $e) {
            $em = $this->kernel->getContainer()->get('doctrine')->getManager();
            $em->getConnection()->close();
            $em->getConnection()->connect();
        }
    }
}
