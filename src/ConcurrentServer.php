<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

use UMA\JsonRpc\Internal\Input;

/**
 * Experimental concurrent Server. Needs the pcntl extension,
 * therefore it can only work in the CLI SAPI.
 *
 * /!\ Probably NOT fit for production usage.
 */
final class ConcurrentServer extends Server
{
    protected function batch(Input $input): ?string
    {
        \assert($input->isArray());

        $children = [];
        $responses = [];
        foreach ($input->data() as $request) {
            $pair = \stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

            $pid = \pcntl_fork();

            if (0 === $pid) {
                \fclose($pair[0]);

                \fwrite($pair[1], $this->single(Input::fromSafeData($request)) . '');

                \fclose($pair[1]);

                exit(0);
            }

            \fclose($pair[1]);

            $children[$pid] = $pair[0];
        }

        while (-1 !== $pid = \pcntl_wait($status)) {
            $socket = $children[$pid];

            if ('' !== $response = \stream_get_contents($socket)) {
                $responses[] = $response;
            }

            \fclose($socket);
        }

        return empty($responses) ?
            null : \sprintf('[%s]', \implode(',', $responses));
    }
}
