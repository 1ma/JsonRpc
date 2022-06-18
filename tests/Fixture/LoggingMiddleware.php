<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use UMA\JsonRpc;
use function json_encode;

final class LoggingMiddleware implements JsonRpc\Middleware
{
    /**
     * @var string[]
     */
    private array $requests;

    /**
     * @var string[]
     */
    private array $responses;

    public function __invoke(JsonRpc\Request $request, JsonRpc\Procedure $next): JsonRpc\Response
    {
        $this->requests[] = json_encode($request);

        $response = $next($request);

        $this->responses[] = json_encode($response);

        return $response;
    }

    /**
     * @return string[]
     */
    public function getSeenRequests(): array
    {
        return $this->requests;
    }

    /**
     * @return string[]
     */
    public function getSeenResponses(): array
    {
        return $this->responses;
    }
}
