<?php

declare(strict_types=1);

namespace Gaby\Shared\Http;

use Psr\Http\Message\ResponseInterface as Response;

trait JsonResponseTrait
{
    /**
     * @param array<string, mixed>|array<int, mixed> $payload
     */
    protected function json(Response $response, array $payload, int $status = 200): Response
    {
        $body = $response->getBody();
        $body->rewind();
        $body->write((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Pragma', 'no-cache')
            ->withStatus($status);
    }
}
