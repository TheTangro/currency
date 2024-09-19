<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\NotificationRequest;
use App\Repository\CurrencyRateRepository;
use App\Repository\NotificationRequestRepository;
use App\Service\Notification\Transport\TransportFactory;
use App\Service\PoisonPillManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'crypto:notification:process',
    description: 'Add a short description for your command',
)]
class ProcessNotifications extends Command
{
    public function __construct(
        private readonly NotificationRequestRepository $notificationRequestRepository,
        private readonly PoisonPillManager $poisonPillManager,
        private readonly LoggerInterface $logger,
        private readonly CurrencyRateRepository $currencyRateRepository,
        private readonly TransportFactory $transportFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        do {
            $this->entityManager->clear();
            $notificationRequests = $this->notificationRequestRepository->findAllNonFinished();

            if (empty($notificationRequests)) {
                usleep(1000);
            } else {
                try {
                    /** @var NotificationRequest $notificationRequest **/
                    foreach ($notificationRequests as $notificationRequest) {
                        $notificationSender = $notificationRequest->getNotification();

                        if (method_exists($notificationSender, 'setCurrencyRateRepository')) {
                            $notificationSender->setCurrencyRateRepository($this->currencyRateRepository);
                        }

                        if (!$notificationSender->isFinished() && $notificationSender->isNeedSend()) {
                            /** @var \App\Entity\NotificationChannel $notificationChannel **/
                            foreach ($notificationRequest->getNotificationChannels() as $notificationChannel) {
                                $transport = $this->transportFactory->createTransport($notificationChannel->getType());
                                $transport->send($notificationChannel, $notificationSender->getText());
                            }

                            if ($notificationSender->isFinished()) {
                                $notificationRequest->setFinished(true);
                            }

                            $notificationSender->updateNotificationData();
                        } elseif ($notificationSender->isFinished()) {
                            $notificationRequest->setFinished(true);
                        }

                        $notificationRequest->setNotification($notificationSender);
                        $this->notificationRequestRepository->save($notificationRequest);
                    }
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage());
                    $io->error($e->getMessage() . PHP_EOL . $e->getTraceAsString());

                    return Command::FAILURE;
                }

                usleep(100);
            }
        } while (!$this->poisonPillManager->isNeedDie());

        return self::SUCCESS;
    }
}