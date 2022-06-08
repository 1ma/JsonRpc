<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use Error;
use LogicException;
use Opis\JsonSchema\FormatContainer;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use TypeError;
use UMA\DIC\Container;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\LoggingMiddleware;
use UMA\JsonRpc\Tests\Fixture\PrimeNumberFormat;
use UMA\JsonRpc\Tests\Fixture\PrimeNumberProcedure;
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

    protected function setUp(): void
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

    public function testValidatorExtension(): void
    {
        $extraFormats = new FormatContainer();
        $extraFormats->add('integer', 'prime', new PrimeNumberFormat());

        $validator = new Validator();
        $validator->setFormats($extraFormats);

        $this->container->set(Validator::class, $validator);
        $this->container->set(PrimeNumberProcedure::class, new PrimeNumberProcedure);
        $this->sut->set('primes', PrimeNumberProcedure::class);

        self::assertSame(
            '{"jsonrpc":"2.0","result":"this is a prime number","id":1}',
            $this->sut->run('{"jsonrpc": "2.0", "method": "primes", "params": {"number": 3}, "id": 1}')
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":2}',
            $this->sut->run('{"jsonrpc": "2.0", "method": "primes", "params": {"number": 4}, "id": 2}')
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":3}',
            $this->sut->run('{"jsonrpc": "2.0", "method": "primes", "params": {"number": "what is even that"}, "id": 3}')
        );
    }

    public function testExceptionBubblesUpOnValidatorExtensionBadUsage(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Call to a member function dataValidation() on string');

        $this->container->set(Validator::class, 'what is even that');
        $this->container->set(Subtractor::class, new Subtractor);
        $this->sut->set('subtract', Subtractor::class);

        $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": ["foo", "bar"], "id": 1}');
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
