<?php

declare(strict_types=1);

namespace App\Service\DataRetrievers;

interface SubscriberInterface
{
    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     *
     * @param callable $callback
     *
     * @return void
     */
    public function subscribe(string $currencyFrom, string $currencyTo, callable $callback): void;
}