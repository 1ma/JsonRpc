<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

use JsonException;
use LogicException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator as OpisValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UMA\JsonRpc\Internal\Assert;
use UMA\JsonRpc\Internal\Input;
use UMA\JsonRpc\Internal\MiddlewareStack;
use stdClass;
use function array_key_exists;
use function array_keys;
use function array_map;
use function assert;
use function count;
use function implode;
use function is_int;
use function json_encode;
use function sprintf;

final class Server
{
    private ContainerInterface $container;
    private array $methods;
    private array $middlewares;
    private ?int $batchLimit;
    private ErrorFormatter $errorFormatter;

    public function __construct(ContainerInterface $container, int $batchLimit = null)
    {
        $this->container = $container;
        $this->batchLimit = $batchLimit;
        $this->methods = [];
        $this->middlewares = [];
        $this->errorFormatter = new ErrorFormatter();
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws JsonException
     */
    public function run(string $raw): ?string
    {
        $input = Input::fromString($raw);

        if (!$input->parsable()) {
            return self::end(Error::parsing());
        }

        if ($input->isArray()) {
            if ($this->tooManyBatchRequests($input)) {
                return self::end(Error::tooManyBatchRequests($this->batchLimit));
            }

            return $this->batch($input);
        }

        return $this->single($input);
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws JsonException
     */
    private function batch(Input $input): ?string
    {
        assert($input->isArray());

        $responses = [];
        foreach ($input->data() as $request) {
            $pseudoInput = Input::fromSafeData($request);

            if (null !== $response = $this->single($pseudoInput)) {
                $responses[] = $response;
            }
        }

        return empty($responses) ?
            null : sprintf('[%s]', implode(',', $responses));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function single(Input $input): ?string
    {
        if (!$input->isRpcRequest()) {
            return self::end(Error::invalidRequest());
        }

        $request = new Request($input);

        if (!array_key_exists($request->method(), $this->methods)) {
            return self::end(Error::unknownMethod($request->id()), $request);
        }

        try {
            $procedure = Assert::isProcedure(
                $this->container->get($this->methods[$request->method()])
            );
        } catch (ContainerExceptionInterface | NotFoundExceptionInterface) {
            return self::end(Error::internal($request->id()), $request);
        }

        if ($procedure->getSpec() instanceof stdClass) {
            $validatorResult = $this->validate($procedure->getSpec(), $request->params());
            if (!$validatorResult->isValid()) {
                return self::end(
                    Error::invalidParams(
                        $request->id(),
                        $this->errorFormatter->format($validatorResult->error())
                    ),
                    $request
                );
            }
        }

        $stack = MiddlewareStack::compose(
            $procedure,
            ...array_map(fn (string $serviceId): Middleware => $this->container->get($serviceId), array_keys($this->middlewares))
        );

        return self::end($stack($request), $request);
    }

    /**
     * @param stdClass $schema The schema to check against the given data.
     * @param mixed $data The data to validate (MUST be decoded JSON data).
     *
     * @return ValidationResult Whether $data conforms to $schema or not
     */
    private function validate(stdClass $schema, mixed $data): ValidationResult
    {
        try {
            return $this->container->get(OpisValidator::class)->validate($data, $schema);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface) {
            return (new OpisValidator())->validate($data, $schema);
        }
    }

    private function tooManyBatchRequests(Input $input): bool
    {
        assert($input->isArray());

        return is_int($this->batchLimit) && $this->batchLimit < count($input->data());
    }

    /**
     * @throws JsonException
     */
    private static function end(Response $response, Request $request = null): ?string
    {
        return $request instanceof Request && null === $request->id() ?
            null : json_encode($response, JSON_THROW_ON_ERROR);
    }
}
