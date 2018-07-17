<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UMA\JsonRpc\Internal\Pipeliner;
use UMA\JsonRpc\Internal\Validator;
use UMA\JsonRpc\Internal\Input;

class Server
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
     * @var Middleware[]
     */
    private $middlewares;

    /**
     * @var int|null
     */
    protected $batchLimit;

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
            throw new \LogicException("Cannot find service '$serviceId' in the container");
        }

        $this->methods[$method] = $serviceId;

        return $this;
    }

    public function pipe(string $serviceId): Server
    {
        if (!$this->container->has($serviceId)) {
            throw new \LogicException("Cannot find service '$serviceId' in the container");
        }

        $this->middlewares[$serviceId] = null;

        return $this;
    }

    public function run(string $raw): ?string
    {
        $input = Input::fromString($raw);

        if (!$input->parsable()) {
            return self::end(Error::parsing());
        }

        if ($input->isArray()) {
            return $this->batch($input);
        }

        return $this->single($input);
    }

    protected function batch(Input $input): ?string
    {
        \assert($input->isArray());

        if ($this->tooManyBatchRequests($input)) {
            return self::end(Error::tooManyBatchRequests($this->batchLimit));
        }

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

    protected function single(Input $input): ?string
    {
        if (!$input->isRpcRequest()) {
            return self::end(Error::invalidRequest());
        }

        $request = new Request($input);

        if (!array_key_exists($request->method(), $this->methods)) {
            return self::end(Error::unknownMethod($request->id()), $request);
        }

        try {
            $procedure = $this->container->get($this->methods[$request->method()]);
        } catch (ContainerExceptionInterface | NotFoundExceptionInterface $e) {
            return self::end(Error::internal($request->id()), $request);
        }

        if (!$procedure instanceof Procedure) {
            return self::end(Error::internal($request->id()), $request);
        }

        $spec = $procedure->getSpec();

        if ($spec instanceof \stdClass && !Validator::validate($spec, $request->params())) {
            return self::end(Error::invalidParams($request->id()), $request);
        }

        try {
            $pipeline = Pipeliner::build($this->container, $procedure, $this->middlewares);
        } catch (\Throwable $t) {
            return self::end(Error::internal($request->id()), $request);
        }

        return self::end($pipeline($request), $request);
    }

    protected function tooManyBatchRequests(Input $input): bool
    {
        \assert($input->isArray());

        return \is_int($this->batchLimit) && $this->batchLimit < \count($input->data());
    }

    protected static function end(Response $response, Request $request = null): ?string
    {
        return $request instanceof Request && null === $request->id() ?
            null : \json_encode($response);
    }
}
