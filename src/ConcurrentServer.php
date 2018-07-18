<?php

declare (strict_types=1);

namespace UMA\JsonRpc;

use Psr\Container\ContainerInterface;
use UMA\JsonRpc\Internal\Input;

/**
 * Experimental concurrent Server. Needs the pcntl extension,
 * therefore it can only work in the CLI SAPI.
 */
class ConcurrentServer extends Server
{
    public function __construct(ContainerInterface $container, int $batchLimit = null)
    {
        if (!\extension_loaded('pcntl')) {
            throw new \RuntimeException('ConcurrentServer relies on the pcntl extension');
        }

        \pcntl_async_signals(true);

        parent::__construct($container, $batchLimit);
    }

    protected function batch(Input $input): ?string
    {
        \assert($input->isArray());

        if ($this->tooManyBatchRequests($input)) {
            return self::end(Error::tooManyBatchRequests($this->batchLimit));
        }

        $children = [];
        $responses = [];
        foreach ($input->decoded() as $request) {
            $pair = \stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

            $pid = \pcntl_fork();

            if (0 === $pid) {
                \fclose($pair[0]);

                \fwrite($pair[1], $this->single(Input::fromSafeData($request)) . "\n");

                \fclose($pair[1]);

                exit(0);
            }

            \fclose($pair[1]);

            $children[$pid] = $pair[0];
        }

        foreach ($children as $pid => $socket) {
            if ('' !== $response = \trim(\fgets($socket))) {
                $responses[] = $response;
            }

            \fclose($socket);
            \pcntl_waitpid($pid, $status);
        }

        return empty($responses) ?
            null : \sprintf('[%s]', \implode(',', $responses));
    }
}
