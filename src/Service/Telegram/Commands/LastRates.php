<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use App\Repository\CurrencyRateRepository;
use App\Repository\ParserProfileRepository;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class LastRates extends AbstractHtmlCommand
{
    public function __construct(
        private readonly ParserProfileRepository $parserProfileRepository,
        private readonly CurrencyRateRepository $currencyRateRepository
    ) {
    }

    public function process(Message $message): string
    {
        $result = '';
        $counter = 0;

        foreach ($this->parserProfileRepository->findAll() as $parserProfile) {
            $lastRow = $this->currencyRateRepository->getLast(
                $parserProfile->getCurrencyFrom(),
                $parserProfile->getCurrencyTo()
            );

            if ($lastRow !== null) {
                $result .= PHP_EOL . sprintf(
                        '%d.  <a>%s => %s</a> (%s) <b>%s</b>',
                        ++$counter,
                        $parserProfile->getCurrencyFrom(),
                        $parserProfile->getCurrencyTo(),
                        $lastRow->getCreatedAt()->format('Y-m-d H:i:s'),
                        $this->formatNumber($lastRow->getRate())
                    );
            }
        }

        return $result;
    }

    private function formatNumber($number, $decimalPlaces = 5) {
        $factor = pow(10, $decimalPlaces);
        $roundedUp = ceil($number * $factor) / $factor;

        return sprintf("%.{$decimalPlaces}f", $roundedUp);
    }

    public function getKeyboard(): ?ReplyKeyboardMarkup
    {
        return null;
    }
}