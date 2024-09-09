<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use App\Repository\CurrencyRateRepository;
use App\Repository\ParserProfileRepository;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class GrabberInfo extends AbstractHtmlCommand
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
            $result .= PHP_EOL . sprintf(
                    '%d. <a>%s => %s</a> rows count: <b>%d</b>',
                    ++$counter,
                    $parserProfile->getCurrencyFrom(),
                    $parserProfile->getCurrencyTo(),
                    $this->currencyRateRepository->getRowsCount(
                        $parserProfile->getCurrencyFrom(),
                        $parserProfile->getCurrencyTo()
                    )
                );
        }

        $result .= PHP_EOL . PHP_EOL . sprintf(
                '<a>Total count: </a> <b>%d</b>',
                $this->currencyRateRepository->getAllRowsCount()
            );

        return $result;
    }

    public function getKeyboard(): ?ReplyKeyboardMarkup
    {
        return null;
    }
}