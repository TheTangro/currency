<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\CurrencyRate;
use App\Service\Notification\SendingStrategy\SendingStrategyInterface;
use DateTime;

class SimpleProfitNotification extends AbstractCurrencyNotification
{
    public function __construct(
        private readonly string $currencyFrom,
        private readonly float $tradeAmount,
        private readonly float $dealCost,
        private readonly float $profitAmount,
        SendingStrategyInterface $sendingStrategy,
        private readonly string $currencyTo = 'USDT',
    ) {
        parent::__construct($sendingStrategy);
    }

    public function isNeedSend(): bool
    {
        $result = false;

        if ($this->currencyRateRepository && $this->validateSendingStrategy()) {
            $last = $this->currencyRateRepository->getLast(
                $this->currencyFrom,
                $this->currencyTo,
            );

            if ($last) {
                $targetProfit = round($this->profitAmount, 2);
                $profit = $this->calculateProfit($last);

                return $this->isFloatEqual($profit, $targetProfit) || $this->isFloatGreaterThan($profit, $targetProfit);
            }
        }

        return $result;
    }

    public function getText(): string
    {
        $profit = $this->profitAmount;
        $last = $this->currencyRateRepository->getLast(
            $this->currencyFrom,
            $this->currencyTo,
        );

        if ($last) {
            $profit = $this->calculateProfit($last);
        }

        return sprintf(
            'Ваша сделка на сумму %s (%s => %s) может быть закрыта с профитом = %s',
            $this->tradeAmount,
            $this->currencyFrom,
            $this->currencyTo,
            $profit
        );
    }

    public function __serialize(): array
    {
        $result = [
            'currencyFrom' => $this->currencyFrom,
            'tradeAmount' => $this->tradeAmount,
            'dealCost' => $this->dealCost,
            'profitAmount' => $this->profitAmount,
            'currencyTo' => $this->currencyTo
        ];

        return array_replace(parent::__serialize(), $result);
    }

    /**
     * @param CurrencyRate $last
     * @return float
     */
    public function calculateProfit(CurrencyRate $last): float
    {
        $cryptoCurrencyAmount = $this->tradeAmount / $this->dealCost;
        $newFiatCost = $cryptoCurrencyAmount * $last->getRate();
        $profit = $newFiatCost - $this->tradeAmount;
        $profit = round($profit, 2);

        return $profit;
    }
}