### [4.0.0] - 2022-06-09

* (Breaking Change) Bumped minimum version of PHP to 8.0
* (Breaking Change) Bumped minimum requirement of psr/container to ^2.0
* (Breaking Change) Bumped minimum requirement of opis/json-schema to ^2.0
* (Breaking Change) Removed ConcurrentServer class
* (Breaking Change) Made Server class final and their attributes private
* (Improvement) Made it possible to redefine the Validator through a PSR-11 container service, contributed by @FTI-Herbert
* (Improvement) Added `getMethods()` to the Server class, contributed by @FTI-Herbert

### [3.0.0] - 2020-12-24

* (Breaking Change) Raised minimum required version to PHP 7.3 and started to test lib for PHP 7.3, 7.4 and 8.0
* (Breaking Change) Dropped simdjson decoding support.
* (Improvement) Updated development dependencies
* (Improvement) Decommissioned Travis CI in favor of Github Actions

### [2.1.3] - 2020-08-24

  * (Improvement) [use static instead of self for late static bindings in Server](https://github.com/1ma/JsonRpc/pull/7)

### [2.1.2] - 2020-01-12

  * (Improvement) Relaxed Server attributes visibility.

### [2.1.1] - 2019-08-12

  * (Improvement) Control Json decoding algorithm with a flag to the JsonRpc Server.

### [2.1.0] - 2019-08-02

  * (Feature) Transparently decode Json payloads with the [simdjson](https://github.com/crazyxman/simdjson_php) PHP bindings when they are available.

### [2.0.0] - 2018-12-05

  * (Breaking Change) Made `Middleware::__invoke()` type safe by passing a `Procedure` instead of `callable` as the second parameter. Requires a typehint rewrite in all descendants.
  * (Breaking Change) Made all classes final except `Server` (because `ConcurrentServer` has to extend it). You must not extend it, though. I'll be watching.
  * (Improvement) `ConcurrentServer` now picks up responses from each child as soon as they finish, instead of always waiting for them in the same order.
  * (Improvement) In `Server::run()`, throw a `TypeError` if the procedure or any middleware service are not valid objects once fetched from the container.
  * (Improvement) Added phpmetrics report. Run it with `composer metrics`.

### [1.0.0] - 2018-07-28

  * (Breaking Change) Renamed `Server::add()` to `Server::set()` to better reflect what it really does.
  * (Breaking Change) Renamed `Procedure::execute()` to `Procedure::__invoke()` to support middlewares.
  * (Breaking Change) Deleted the `Error::custom()` method since it is now redundant.
  * (Feature) Added an experimental ConcurrentServer based on the PCNTL extension.
  * (Feature) Added middleware support.
  * (Feature) Added an optional batch limit option to the `Server` constructor.
  * (Improvement) Opened up the `Error` constructor.
  * (Improvement) `Request` now implements `JsonSerializable`, just like `Success` and `Error`.
  * (Improvement) Enabled advanced Scrutinizer CI analysis.

### [0.9.0] - 2018-06-19

  * Initial pre-release

[4.0.0]: https://github.com/1ma/JsonRpc/compare/v3.0.0...v4.0.0
[3.0.0]: https://github.com/1ma/JsonRpc/compare/v2.1.3...v3.0.0
[2.1.3]: https://github.com/1ma/JsonRpc/compare/v2.1.2...v2.1.3
[2.1.2]: https://github.com/1ma/JsonRpc/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/1ma/JsonRpc/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/1ma/JsonRpc/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/1ma/JsonRpc/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/1ma/JsonRpc/compare/v0.9.0...v1.0.0
[0.9.0]: https://github.com/1ma/JsonRpc/commit/081b048bb5a5a58235953dd42772ff31256a9e49
