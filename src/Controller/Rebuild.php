<?php

declare(strict_types=1);

namespace App\Controller;

use App\Kernel;
use App\Service\ConfigManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

class Rebuild extends AbstractController
{
    #[Route('/rebuild', name: 'rebuild_webhook', methods: ['POST'])]
    public function execute(
        Request $request,
        LoggerInterface $logger,
        Kernel $kernel,
        ConfigManager $configManager
    ): Response {
        $payload = json_decode($request->getContent(), true);
        $signature = (string) $request->headers->get('X-Hub-Signature-256');
        $secret = $configManager->getGithubSecret();
        $signatureParts = explode('=', $signature);

        if (count($signatureParts) != 2) {
            throw new BadRequestHttpException('signature has invalid format');
        }

        $computedSign = hash_hmac('sha256', $request->getContent(), $secret);

        if (hash_equals($computedSign, $signatureParts[1])) {
            $logger->info('GitHub Webhook received: ' . $request->getContent());
            $this->runRebuild($kernel, $logger);
            return new Response('Webhook processed', Response::HTTP_OK);
        } else {
            return new Response('Invalid signature', Response::HTTP_FORBIDDEN);
        }
    }

    private function runRebuild(Kernel $kernel, LoggerInterface $logger): void
    {
        $process = new Process(
            command: [
                '/bin/bash',
                'bin/rebuild.sh',
                '2>&1'
            ],
            cwd: $kernel->getProjectDir(),
            timeout: null
        );
        $process->start();
        
        while ($process->isRunning()) {
            sleep(1);
        }

        $logger->info('Rebuild successfully: ' . $process->getOutput());
    }
}