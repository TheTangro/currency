<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Api\NotificationChannelType;
use App\Entity\NotificationChannel;
use App\Entity\NotificationRequest;
use App\Repository\NotificationRequestRepository;
use App\Service\Notification\SendingStrategy\MultipleSendingStrategy;
use App\Service\Notification\SimpleProfitNotification as SimpleProfitNotificationSender;
use App\Service\Telegram\Commands\CommandInterface;
use App\Service\Telegram\Commands\SendCouldNotRecognizeCommand;
use App\Service\Telegram\Commands\SendOk;
use TelegramBot\Api\Types\Message;

class SimpleProfitNotification implements ProcessorInterface
{
    public const MAIN_PATTERN
        = '/.*?вошел.*?([A-Z]+).*?([0-9.,]+).*?([0-9.,]+).*?[Нн]апомни.*?(прибыль|убыток).*?([0-9.,]+).*$/mu';

    public function __construct(
        private readonly NotificationRequestRepository $notificationRequestRepository
    ) {
    }

    public function isSupported(string $phrase): bool
    {
        return (bool) preg_match(self::MAIN_PATTERN, $phrase);
    }

    public function process(string $phrase, Message $message): ?CommandInterface
    {
        preg_match(self::MAIN_PATTERN, $phrase, $matches);

        if (count($matches) === 6) {
            [,$currencyFrom, $tradeAmount, $dealCost, $profitType, $profitAmount] = $matches;
            $profitAmount = (float) trim($profitAmount, ',.');

            if ($profitType !== 'прибыль') {
                $profitAmount = -$profitAmount;
            }

            $tradeAmount = (float) trim($tradeAmount, ',.');
            $dealCost = (float) trim($dealCost, ',.');

            if (preg_match('/.*повторять.*?(\d+).*?(частот.*?(\d+).*)/us', $phrase, $frequencyMatches)) {
                if (count($frequencyMatches) === 2) {
                    [, $repeatAmount] = $frequencyMatches;
                    $repeatAmount = (int) $repeatAmount;
                }  elseif (count($frequencyMatches) === 4) {
                    [, $repeatAmount,,$frequency] = $frequencyMatches;
                    $repeatAmount = (int) $repeatAmount;
                    $frequency = (int) $frequency;
                }
            }

            $simpleProfitNotification = new SimpleProfitNotificationSender(
                $currencyFrom,
                $tradeAmount,
                $dealCost,
                $profitAmount,
                new MultipleSendingStrategy($repeatAmount ?? 1, $frequency ?? 1)
            );
            $notificationRequest = new NotificationRequest();
            $notificationChannel = new NotificationChannel();
            $notificationChannel->setType(NotificationChannelType::TELEGRAM);
            $notificationChannel->setPayload([
                'chat_id' => $message->getChat()->getId(),
            ]);
            $notificationRequest->setNotification($simpleProfitNotification);
            $notificationRequest->getNotificationChannels()->add($notificationChannel);
            $this->notificationRequestRepository->save($notificationRequest);

            return new SendOk;
        }

        return new SendCouldNotRecognizeCommand;
    }
}