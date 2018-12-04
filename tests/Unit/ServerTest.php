<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use TypeError;
use UMA\DIC\Container;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\LoggingMiddleware;
use UMA\JsonRpc\Tests\Fixture\Subtractor;

final class ServerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Server
     */
    private $sut;

    protected function setUp()
    {
        $this->container = new Container;
        $this->sut = new Server($this->container);
    }

    public function testAddingANonExistentProcedureService(): void
    {
        $this->expectException(LogicException::class);

        $this->sut->set('subtract', Subtractor::class);
    }

    public function testAddingANonExistentMiddlewareService(): void
    {
        $this->expectException(LogicException::class);

        $this->sut->attach(LoggingMiddleware::class);
    }

    public function testInvalidProcedureService(): void
    {
        $this->expectException(TypeError::class);

        $this->container->set(Subtractor::class, 'this is not a Procedure!');

        $this->sut->set('subtract', Subtractor::class);

        $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');
    }

    public function testInvalidMiddleware(): void
    {
        $this->expectException(TypeError::class);

        $this->container->set(Subtractor::class, new Subtractor);
        $this->container->set(LoggingMiddleware::class, 'This is not a Middleware!');

        $this->sut->set('subtract', Subtractor::class);
        $this->sut->attach(LoggingMiddleware::class);

        $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');
    }

    public function testInvalidParams(): void
    {
        $this->container->set(Subtractor::class, new Subtractor);

        $this->sut->set('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":1}',
            $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": ["foo", "bar"], "id": 1}')
        );
    }

    public function testTooManyBatchRequestsSent(): void
    {
        $this->container->set(Subtractor::class, new Subtractor);

        $limitedServer = new Server($this->container, 1);
        $limitedServer->set('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32000,"message":"Too many batch requests sent to server","data":{"limit":1}},"id":null}',
            $limitedServer->run('[
              {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1},
              {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 2}
            ]')
        );
    }

    public function testPsr11ContainerException(): void
    {
        /** @var MockObject|Container $container */
        $container = $this->getMockBuilder(Container::class)
            ->setMethods(['get'])
            ->getMock();

        $container->expects(self::once())
            ->method('get')
            ->with(Subtractor::class)
            ->will(self::throwException(new class extends \RuntimeException implements ContainerExceptionInterface {}));

        $container->set(Subtractor::class, new Subtractor);

        $sut = new Server($container);
        $sut->set('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":1}',
            $sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}')
        );
    }
}
