<?php

declare(strict_types=1);

namespace App\Controller;

use App\Kernel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

class Rebuild extends AbstractController
{
    #[Route('/rebuild', name: 'rebuild_webhook', methods: ['POST'])]
    public function execute(
        Request $request,
        LoggerInterface $logger,
        Kernel $kernel
    ): Response {
        $payload = json_decode($request->getContent(), true);
        $signature = $request->headers->get('X-Hub-Signature-256');
        $secret = (string) getenv('GITHUB_SECRET');
        $computedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        if (hash_equals($computedSignature, $signature)) {
            $logger->info('GitHub Webhook received: ' . $request->getContent());
            $this->runRebuild($kernel);
            return new Response('Webhook processed', Response::HTTP_OK);
        } else {
            return new Response('Invalid signature', Response::HTTP_FORBIDDEN);
        }
    }

    private function runRebuild(Kernel $kernel): void
    {
        $process = new Process(
            command: [
                '/bin/bash',
                'bin/rebuild.sh',
                '2>&1',
                sprintf('>%s/system.log', $kernel->getLogDir())
            ],
            cwd: $kernel->getProjectDir(),
            timeout: null
        );
        $process->start();
        
        while ($process->isRunning()) {
            sleep(1);
        }
    }
}