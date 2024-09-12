<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ConfigManager;
use App\Service\Telegram\Commands\CommandInterface;
use App\Service\Telegram\NaturalLanguage\ProcessorInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;

class TelegramBotHandle extends AbstractController
{
    public function __construct(
        private readonly ProcessorInterface $naturalLanguageProcessor
    ) {
    }

    #[Route('/bot-handle', name: 'telegram-bot-handle', methods: ['POST'])]
    public function execute(
        LoggerInterface $logger,
        ConfigManager $configManager,
    ): Response {
        $bot = new BotApi($configManager->getTelegramBotApiToken());
        $client = new TelegramClient($configManager->getTelegramBotApiToken());
        $client->on(
            fn (Update $update) => $this->handleUpdate($update, $bot, $this->container),
            fn() => true
        );
        $client->run();

        return new Response();
    }

    private function handleUpdate(Update $update, BotApi $client, ContainerInterface $container): void
    {
        $message = $update->getMessage();
        $initialMarkup = new ReplyKeyboardMarkup(
            [
                [
                    '/grabber_info',
                    '/last_rates',
                ]
            ],
            true,
            true
        );

        if ($message && $message->getChat()?->getId()) {
            $messageText = $message->getText();

            switch (true) {
                case str_starts_with($messageText, '/'):
                    $this->processCommand($message, $client, $initialMarkup);

                    return;
                case $this->naturalLanguageProcessor->isSupported($messageText):
                    $this->processNaturalLanguage($message, $client, $initialMarkup);

                    return;
                default:
                    $this->sayHello($message, $client, $initialMarkup);
            }
        }
    }

    private function sayHello(Message $message, BotApi $client, $initialMarkup): void
    {
        $client->sendMessage(
            chatId: $message->getChat()->getId(),
            text: 'Hi! What you need?',
            replyMarkup: $initialMarkup
        );
    }

    private function processNaturalLanguage(Message $message, BotApi $client, $initialMarkup): void
    {
        $command = $this->naturalLanguageProcessor->process($message->getText(), $message);

        if ($command) {
            $client->sendMessage(
                chatId: $message->getChat()->getId(),
                text: $command->process($message),
                parseMode: $command->getParseMode(),
                replyMarkup: $command->getKeyboard() ?: $initialMarkup
            );
        } else {
            $this->sayHello($message, $client, $initialMarkup);
        }
    }

    private function processCommand(Message $message, BotApi $client, $initialMarkup): void
    {
        $messageText = $message->getText();
        $parts = explode('_', ltrim($messageText, '\/'));
        $className = join('', array_map('ucfirst', $parts));
        $fullClassName = '\\App\\Service\\Telegram\\Commands\\' . $className;
        $serviceName = sprintf('Telegram%sCommand', $className);

        if (class_exists($fullClassName) && is_subclass_of($fullClassName, CommandInterface::class)) {
            try {
                /** @var \App\Service\Telegram\Commands\CommandInterface $command * */
                $command = $this->container->get($fullClassName);
            } catch (\Throwable $e) {
                $command = new $fullClassName;
            }

            $response = $command->process($message);
            $client->sendMessage(
                chatId: $message->getChat()->getId(),
                text: $response,
                parseMode: $command->getParseMode(),
                replyMarkup: $command->getKeyboard() ?: $initialMarkup
            );
        } else {
            $this->sayHello($message, $client, $initialMarkup);
        }
    }
}
