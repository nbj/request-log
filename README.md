# request-log
[![Laravel Octane Compatible](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://github.com/laravel/octane)

## Octane specifics
If you are running octane with openswoole or swoole, the logs will not be output into stdout for performance reasons.

Instead, the logs will be written to the configured `octaneLogFolder`, with a rotating file logger for each worker.
