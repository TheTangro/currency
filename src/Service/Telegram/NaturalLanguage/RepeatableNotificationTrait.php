<?php

namespace App\Service\Telegram\NaturalLanguage;

use App\Service\Notification\SendingStrategy\MultipleSendingStrategy;
use App\Service\Notification\SendingStrategy\SendingStrategyInterface;

trait RepeatableNotificationTrait
{
    protected function generateNotificationStrategy(string $phrase): SendingStrategyInterface
    {
        if (preg_match('/.*повторять.*?(\d+).*?(частот.*?(\d+).*)/us', $phrase, $frequencyMatches)) {
            if (count($frequencyMatches) === 2) {
                [, $repeatAmount] = $frequencyMatches;
                $repeatAmount = (int) $repeatAmount;
            }  elseif (count($frequencyMatches) === 4) {
                [, $repeatAmount,,$frequency] = $frequencyMatches;
                $repeatAmount = (int) $repeatAmount;
                $frequency = (int) $frequency;
            }

            return new MultipleSendingStrategy($repeatAmount ?? 1, $frequency ?? 1);
        } else {
            return new class implements SendingStrategyInterface
            {
                public function getRepeatsAmount(): int
                {
                    return 1;
                }

                public function getFrequency(): int
                {
                    return 1;
                }
            };
        }
    }
}