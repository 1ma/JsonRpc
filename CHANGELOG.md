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

[2.1.0]: https://github.com/1ma/JsonRpc/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/1ma/JsonRpc/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/1ma/JsonRpc/compare/v0.9.0...v1.0.0
[0.9.0]: https://github.com/1ma/JsonRpc/commit/081b048bb5a5a58235953dd42772ff31256a9e49
