<?php

declare(strict_types=1);

namespace App\Service\DataRetrievers;

use App\Entity\CurrencyRate;
use App\Exception\MaxMessagesExceedException;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;

class BybitSubscriber implements SubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function subscribe(string $currencyFrom, string $currencyTo, callable $callback): void
    {
        $eventLoop = \React\EventLoop\Loop::get();
        $connector = new \Ratchet\Client\Connector($eventLoop);

        $connector('wss://stream.bybit.com/v5/public/spot')
            ->then(
                function (WebSocket $conn) use ($currencyFrom, $currencyTo, $callback, $eventLoop) {
                    $this->logger->info('Connection has been established');
                    $subscribeMessage = json_encode([
                        'op' => 'subscribe',
                        'args' => [sprintf('tickers.%s%s', $currencyFrom, $currencyTo)],
                    ]);
                    $conn->send($subscribeMessage);

                    $conn->on('message', function ($message) use ($conn, $currencyFrom, $currencyTo, $callback) {
                        $errorCount = 0;

                        try {
                            $errorCount = 0;
                            $payload = json_decode($message->getPayload(), true);
                            $rate = $payload['data']['lastPrice'] ?? null;

                            if ($rate !== null) {
                                $currencyRate = new CurrencyRate();
                                $currencyRate->setCreatedAt(new \DateTimeImmutable());
                                $currencyRate->setCurrencyFrom($currencyFrom);
                                $currencyRate->setCurrencyTo($currencyTo);
                                $currencyRate->setRate($rate);

                                $callback($currencyRate);
                            }
                        } catch (MaxMessagesExceedException $e) {
                            throw $e;
                        } catch (\Throwable $e) {
                            if (++$errorCount >= 100) {
                                throw $e;
                            }
                        }
                    });

                    // Обрабатываем закрытие соединения
                    $conn->on('close', function ($code = null, $reason = null) use ($eventLoop) {
                        $eventLoop->stop();
                    });
                },
                function (\Exception $e) use ($eventLoop) {
                    $this->logger->error('Could not subscribe: ' . $e->getMessage());

                    $eventLoop->stop();
                }
            );

    }
}