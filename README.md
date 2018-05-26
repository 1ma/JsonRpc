# JsonRpc

[![Build Status](https://travis-ci.org/1ma/JsonRpc.svg?branch=master)](https://travis-ci.org/1ma/JsonRpc) [![Code Coverage](https://scrutinizer-ci.com/g/1ma/JsonRpc/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/1ma/JsonRpc/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/1ma/JsonRpc/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/1ma/JsonRpc/?branch=master)

A [JSON-RPC 2.0] implementation for PHP 7.1

## Installation

```bash
$ composer require uma/json-rpc
```

## About

### Features

* Service lazy-loading
* Built-in parameter validation with `league/json-guard`
* Fully implemented spec

### JSON-RPC 2.0 versus HTTP REST

#### Simplicity

#### Batch processing

#### Decoupled from the transport layer

## Usage

### Implementing procedures

```php
namespace My\Project;

use UMA\JsonRpc\Request;
use UMA\JsonRpc\Response;
use UMA\JsonRpc\Procedure;
use UMA\JsonRpc\Success;

class Subtractor implements Procedure
{
    public function execute(Request $request): Response
    {
        $params = $request->params();

        return new Success(
            $request->id(),
            $params->minuend - $params->subtrahend
        );
    }

    public function getSpec(): ?\stdClass
    {
        return \json_decode(<<<'JSON'
{
  "$schema": "https://json-schema.org/draft-07/schema#",

  "type": "object",
  "required": ["minuend", "subtrahend"],
  "additionalProperties": false,
  "properties": {
    "minuend": { "type": "integer" },
    "subtrahend": { "type": "integer" }
  }
}
JSON
);
    }
}
```

### Setting up the JSON-RPC server

```php
use A\Psr11\Compatible\Container;
use My\Project\Space\Subtractor;
use UMA\JsonRpc\Server;

$container = new Container();

$container[Subtractor::class] = function(Container $container) {
    return new Subtractor();
};

$server = new Server($container);
$server->add('subtract', Subtractor::class);

var_dump($server->run('{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 123}'));
// string(38) '{"jsonrpc":"2.0","result":19,"id":123}'
```

### Bridging with HTTP

#### Slim 3 example

```php
use My\Project\Adder;
use My\Project\Subtractor;
use UMA\JsonRpc\Procedure;
use UMA\JsonRpc\Server;
use Pimple\Psr11\Container as Psr11Decorator;
use Slim\App;
use Slim\Container;
use Slim\Http;

require_once __DIR__ . '/vendor/autoload.php';

$container = new Container();

$container[Adder::class] = function(): Procedure {
    return new Adder();
};

$container[Subtractor::class] = function(): Procedure {
    return new Subtractor();
};

$container[Server::class] = function(Container $container) {
    // Slim's container -which is based on Pimple- is not PSR-11
    // compliant, but there is a PSR-11 wrapper available. 
    $server = new Server(new Psr11Decorator($container));

    $server
        ->add('add', Adder::class)
        ->add('subtract', Subtractor::class);

    return $server;
};

$app = new App($container);

$app->post('/json-rpc', function (Http\Request $request, Http\Response $response) use ($container) {
    $jsonInput = (string) $request->getBody();
    $jsonOutput = $container->get(Server::class)->run($jsonInput);

    if (null === $jsonOutput) {
        // Return an HTTP 204 OK with Empty Response
        // (JSON-RPC request was a Notification)
        return $response->withStatus(204);
    }

    $response->getBody()->write($jsonOutput);

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->run();
```

### Best Practices

#### Rely on Json schemas to validate the params

While it is not mandatory to return a JSON Schema from your procedures it is
highly recommended to do so, because then your procedures can assume that the
input parameters it receives are always valid (according to your definition of "valid").

If you are not familiar with JSON Schemas there's a very good introduction at [Understanding JSON Schema].

#### Defer actual work whenever possible

Since PHP is a language that does not have "mainstream" support for concurrent programming,
whenever the server receives a batch request it has to process every sub-request sequentially,
and this can add up the total response time.

Hence, a JSON-RPC server served over HTTP should strive to defer any actual work by
relying, for instance, on a work queue such as `Beanstalkd` or `RabbitMQ`. A second out-of-band
JSON-RPC server should then consume the queue and do the actual work.

## References

http://www.jsonrpc.org/specification
https://spacetelescope.github.io/understanding-json-schema


[JSON-RPC 2.0]: http://www.jsonrpc.org/specification
[Understanding JSON Schema]: https://spacetelescope.github.io/understanding-json-schema
