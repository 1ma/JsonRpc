<?php

declare (strict_types=1);

namespace UMA\JsonRpc\Tests\Functional;

use PHPUnit\Framework\TestCase;
use UMA\DIC\Container;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\NotificationMiddleware;
use UMA\JsonRpc\Tests\Fixture\Subtractor;

class MiddlewaresTest extends TestCase
{
    /**
     * @var Server
     */
    private $sut;

    /**
     * @var NotificationMiddleware
     */
    private $middleware;

    protected function setUp()
    {
        $this->middleware = new NotificationMiddleware();

        $container = new Container([
            Subtractor::class => new Subtractor(),
            NotificationMiddleware::class => $this->middleware
        ]);

        $this->sut = (new Server($container))
            ->set('subtract', Subtractor::class)
            ->pipe(NotificationMiddleware::class);
    }

    public function testMiddleware(): void
    {
        $response = $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');

        self::assertFalse($this->middleware->lastRequestWasANotification());
        self::assertNotNull($response);

        $response = $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23]}');

        self::assertTrue($this->middleware->lastRequestWasANotification());
        self::assertNull($response);
    }
}
