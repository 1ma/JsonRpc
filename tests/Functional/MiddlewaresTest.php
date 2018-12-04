<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Functional;

use PHPUnit\Framework\TestCase;
use UMA\DIC\Container;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\LoggingMiddleware;
use UMA\JsonRpc\Tests\Fixture\Subtractor;

final class MiddlewaresTest extends TestCase
{
    /**
     * @var Server
     */
    private $sut;

    /**
     * @var LoggingMiddleware
     */
    private $middleware;

    protected function setUp()
    {
        $this->middleware = new LoggingMiddleware();

        $container = new Container([
            Subtractor::class => new Subtractor(),
            LoggingMiddleware::class => $this->middleware
        ]);

        $this->sut = (new Server($container))
            ->set('subtract', Subtractor::class)
            ->attach(LoggingMiddleware::class);
    }

    public function testMiddleware(): void
    {
        $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');

        self::assertSame([
            '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}'
        ], $this->middleware->getSeenRequests());

        self::assertSame([
            '{"jsonrpc":"2.0","result":19,"id":1}'
        ], $this->middleware->getSeenResponses());

        self::assertNull($this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23]}'));

        self::assertSame([
            '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}',
            '{"jsonrpc":"2.0","method":"subtract","params":[42,23]}'
        ], $this->middleware->getSeenRequests());

        self::assertSame([
            '{"jsonrpc":"2.0","result":19,"id":1}',
            '{"jsonrpc":"2.0","result":19,"id":null}'
        ], $this->middleware->getSeenResponses());
    }
}
