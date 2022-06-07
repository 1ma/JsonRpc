<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

use LogicException;
use Opis\JsonSchema\Validator as OpisValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use TypeError;
use UMA\JsonRpc\Internal\Assert;
use UMA\JsonRpc\Internal\Input;
use UMA\JsonRpc\Internal\MiddlewareStack;
use UMA\JsonRpc\Internal\Validator;

final class Server
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $methods;

    /**
     * @var string[]
     */
    private $middlewares;

    /**
     * @var int|null
     */
    private $batchLimit;

    public function __construct(ContainerInterface $container, int $batchLimit = null)
    {
        $this->container = $container;
        $this->batchLimit = $batchLimit;
        $this->methods = [];
        $this->middlewares = [];
    }

    public function set(string $method, string $serviceId): Server
    {
        if (!$this->container->has($serviceId)) {
            throw new LogicException("Cannot find service '$serviceId' in the container");
        }

        $this->methods[$method] = $serviceId;

        return $this;
    }

    public function attach(string $serviceId): Server
    {
        if (!$this->container->has($serviceId)) {
            throw new LogicException("Cannot find service '$serviceId' in the container");
        }

        $this->middlewares[$serviceId] = null;

        return $this;
    }

    /**
     * @throws TypeError
     */
    public function run(string $raw): ?string
    {
        $input = Input::fromString($raw);

        if (!$input->parsable()) {
            return static::end(Error::parsing());
        }

        if ($input->isArray()) {
            if ($this->tooManyBatchRequests($input)) {
                return static::end(Error::tooManyBatchRequests($this->batchLimit));
            }

            return $this->batch($input);
        }

        return $this->single($input);
    }

    private function batch(Input $input): ?string
    {
        \assert($input->isArray());

        $responses = [];
        foreach ($input->data() as $request) {
            $pseudoInput = Input::fromSafeData($request);

            if (null !== $response = $this->single($pseudoInput)) {
                $responses[] = $response;
            }
        }

        return empty($responses) ?
            null : \sprintf('[%s]', \implode(',', $responses));
    }

    /**
     * @throws TypeError
     */
    private function single(Input $input): ?string
    {
        if (!$input->isRpcRequest()) {
            return static::end(Error::invalidRequest());
        }

        $request = new Request($input);

        if (!\array_key_exists($request->method(), $this->methods)) {
            return static::end(Error::unknownMethod($request->id()), $request);
        }

        try {
            $procedure = Assert::isProcedure(
                $this->container->get($this->methods[$request->method()])
            );
        } catch (ContainerExceptionInterface | NotFoundExceptionInterface $e) {
            return static::end(Error::internal($request->id()), $request);
        }

        if ($procedure->getSpec() instanceof stdClass && !$this->validate($procedure->getSpec(), $request->params())) {
            return static::end(Error::invalidParams($request->id()), $request);
        }

        $stack = MiddlewareStack::compose(
            $procedure,
            ...\array_map(function(string $serviceId) {
                return $this->container->get($serviceId);
            }, \array_keys($this->middlewares))
        );

        return static::end($stack($request), $request);
    }

    /**
     * @param stdClass|null $schema The schema to check against the given data.
     * @param mixed $data The data to validate (MUST be decoded JSON data).
     *
     * @return bool Whether $data conforms to $schema or not
     */
    private function validate(stdClass $schema, $data):bool
    {
        if (!$this->container->has(OpisValidator::class)) {
            return Validator::validate($schema, $data);
        }
        try {
            return $this->container->get(OpisValidator::class)->dataValidation($data, $schema)->isValid();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return false;
        }
    }

    private function tooManyBatchRequests(Input $input): bool
    {
        \assert($input->isArray());

        return \is_int($this->batchLimit) && $this->batchLimit < \count($input->data());
    }

    private static function end(Response $response, Request $request = null): ?string
    {
        return $request instanceof Request && null === $request->id() ?
            null : \json_encode($response);
    }
}
