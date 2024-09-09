<?php

declare(strict_types=1);

namespace App\Controller;

use App\Kernel;
use App\Service\ConfigManager;
use App\Service\Telegram\Commands\CommandInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;

class TelegramBotHandle extends AbstractController
{
    #[Route('/bot-handle', name: 'telegram-bot-handle', methods: ['POST'])]
    public function execute(
        LoggerInterface $logger,
        Kernel $kernel,
        ConfigManager $configManager
    ): Response {
        $bot = new BotApi($configManager->getTelegramBotApiToken());
        $client = new \TelegramBot\Api\Client($configManager->getTelegramBotApiToken());
        $client->on(fn (
            Update $update) => $this->handleUpdate($update, $bot, $kernel->getContainer()),
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

        if ($message->getChat()?->getId()) {
            switch ($message->getText()) {
                case '/start':
                    $client->sendMessage(
                        chatId: $message->getChat()->getId(),
                        text: 'Hi! What you need?',
                        replyMarkup: $initialMarkup
                    );

                    return;
                default:
                    if (str_starts_with($message->getText(), '/')) {
                        $parts = explode('_', ltrim($message->getText(), '\/'));
                        $className = join('', array_map('ucfirst', $parts));
                        $fullClassName = '\\App\\Service\\Telegram\\Commands\\' . $className;
                        $serviceName = sprintf('Telegram%sCommand', $className);

                        if (class_exists($fullClassName) && is_subclass_of($fullClassName, CommandInterface::class)) {

                            try {
                                /** @var \App\Service\Telegram\Commands\CommandInterface $command * */
                                $command = $container->get($serviceName);
                            } catch (\Throwable $e) {
                                $command = new $fullClassName;
                            }

                            $response = $command->process($message);
                            $client->sendMessage(
                                chatId: $message->getChat()->getId(),
                                text: $response,
                                parseMode: $command->getParseMode(),
                                replyMarkup: $command->getKeyboard()
                            );
                        }
                    } else {
                        $client->sendMessage(
                            chatId: $message->getChat()->getId(),
                            text: 'Hi! What you need?',
                            replyMarkup: $initialMarkup
                        );
                    }
            }
        }
    }
}