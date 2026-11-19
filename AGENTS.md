# AGENTS.md

Guidance for coding agents working in this repository.

## What this is

`ambroisemaupate/intervention-request`: a PHP library (and Docker image) that resizes/transforms images on the fly from URL query params (or short URLs like `/assets/f100x100-q50/photo.jpg`), on top of `intervention/image` v3, with a file cache. Open source on GitHub. PHP >= 8.2, CI matrix 8.2 to 8.5.

## Commands

Run PHP tooling through Docker, in the `intervention` compose service. `compose.override.yml` (copied from `.dist`) builds the `php-dev` target, mounts `./` on `/app` and serves on `localhost:8080` with `IR_DEBUG=1`:

```sh
make test              # php-cs-fixer fix + phpstan + phpunit (all)
make test-unit         # tests/Processor only
make test-functional   # tests/Functional only

# single test
docker compose run --rm --entrypoint= intervention php -d memory_limit=-1 vendor/bin/phpunit --filter testName tests/Processor/ImageFitProcessingTest.php

make bake              # build and push multi-arch images (php-fpm+nginx and FrankenPHP)
```

CI (`.github/workflows/run-test.yml`) runs `phpunit tests/`, `php-cs-fixer check` (with `PHP_CS_FIXER_IGNORE_ENV=1`) and `phpstan analyse -c phpstan.neon` (level max, `src/` only).

## Architecture

Everything is wired through a Symfony `EventDispatcher` built inside `InterventionRequest::__construct()`; there is no DI container.

- **Request flow**: `InterventionRequest::handleRequest()` requires an `image` query param, dispatches `RequestEvent`; the first subscriber that sets a Response wins, then `ResponseEvent` lets listeners alter headers. Exceptions map to JSON 404/400/500.
- **Response producers** (subscribers on `RequestEvent`, priority decides who answers): `StreamNoProcessListener` (raw passthrough), `Cache\FileCache`, `Cache\PassThroughFileCache` (writes into a public folder so nginx serves next hits; only with `ShortUrlExpander`), `NoCacheImageRequestSubscriber`.
- **Processing**: `Processor\ChainProcessor` reads the image, dispatches `ImageBeforeProcessEvent`, runs each `Processor` (one per query param: `fit`, `crop`, `width`, `hotspot`...), then `ImageAfterProcessEvent`. **Processor order in `getChainProcessor()` matters** (rotate before crop/fit, flip after). A new processor must be added there.
- **Post-save optimizers** (`Listener/*`, on `ImageSavedEvent`): jpegoptim, pngquant, oxipng, pingo, kraken, tinify. Enabled only when the binary path is configured; pingo replaces the others.
- **File resolution**: `FileResolverInterface` with `LocalFileResolver` or `FlysystemFileResolver` (S3 via async-aws).
- **Short URLs**: `ShortUrlExpander` turns `/{ignorePath}/{shortcuts}/{file}` into query params (shortcut letters are listed in README).
- **Config**: `Configuration` is a plain setter bag. Entry points `public/index.php` (php-fpm, reads `IR_*` env vars) and `public/worker.php` (FrankenPHP worker, copied over `index.php` in the `frankenphp-prod` image). `bin/intervention` exposes the garbage collector commands.

## Tests

- `tests/Processor`: one processor applied to an image from `public/images`, asserted by **md5 of the GD output**. Upgrading `intervention/image` or GD can legitimately change hashes.
- `tests/Functional`: full `InterventionRequest` with short URLs (`/assets/...`), asserting the response content md5.
- Both write into `tests/temp/` and clean it in `tearDown`.

## Releases

git flow on GitHub: `hotfix/*` and `release/*` branches are started by a human. Changelog generated with git-cliff (`cliff.toml`), so commits follow Conventional Commits. Breaking changes go in `UPGRADE.md`.
