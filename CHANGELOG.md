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

[1.0.0]: https://github.com/1ma/JsonRpc/compare/v0.9.0...v1.0.0
[0.9.0]: https://github.com/1ma/JsonRpc/commit/081b048bb5a5a58235953dd42772ff31256a9e49
