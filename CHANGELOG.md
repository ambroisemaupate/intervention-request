# Changelog

All notable changes to *Intervention Request* will be documented in this file.

## [7.0.1](https://github.com/ambroisemaupate/intervention-request/compare/v7.0.0...v7.0.1) - 2025-07-23

### Bug Fixes

- **(ImageEncoder)** update ImageEncoder to use resource for filesystem dump - ([7822a87](https://github.com/ambroisemaupate/intervention-request/commit/7822a874c6f6d3f6390ccbcf0c3d45cb4783e56f)) - Ambroise Maupate

## [7.0.0](https://github.com/ambroisemaupate/intervention-request/compare/v6.0.3...v7.0.0) - 2025-07-23

### ⚠ Breaking changes

- Please change your docker container volume configuration from `/var/www/html/web` to `/app/public`.

Added FrankenPHP build

### Bug Fixes

- Allow crop processor without width or height parameters - fix #34 - ([9db0997](https://github.com/ambroisemaupate/intervention-request/commit/9db09975318b0bcf988f2988bbeb6c96543d9a05)) - Ambroise Maupate

### Documentation

- Update README with test grid section and add new test.html for image processing tests - ([5c8056f](https://github.com/ambroisemaupate/intervention-request/commit/5c8056fbc6ef88956e317c48d67d1595b50c1b4c)) - Ambroise Maupate

### Features

- **(docker)** [**breaking**] Use PHP 8.4 and moved directories from /var/www/html to /app and ./web to ./public - ([2e1a71c](https://github.com/ambroisemaupate/intervention-request/commit/2e1a71c84a1ed5a04d1bae78649a7d450647935e)) - Ambroise Maupate
- **(docker)** Add intervention-frankenphp target and update .gitignore for public/images - ([c6d0bd2](https://github.com/ambroisemaupate/intervention-request/commit/c6d0bd2365c2845e91f06e71ea410aa9a9a4d828)) - Ambroise Maupate
- Added new hotspot parameter to define hotspot area in addition of its center - ([71153e0](https://github.com/ambroisemaupate/intervention-request/commit/71153e0308c9f71bccb22d426a3b9ff4f05fb428)) - Ambroise Maupate
- FrankenPHP worker mode and Caddyfiles for development and production - ([25d69a3](https://github.com/ambroisemaupate/intervention-request/commit/25d69a34e027ee41d0caa850e950052d0287efe6)) - Ambroise Maupate

### Refactor

- Refactor file handling in cache and image encoder to use Symfony Filesystem component - ([6972db3](https://github.com/ambroisemaupate/intervention-request/commit/6972db3dbe3ed5f1ad901df77b0f079638314823)) - Ambroise Maupate

## [6.0.3](https://github.com/ambroisemaupate/intervention-request/compare/v6.0.2...v6.0.3) - 2025-07-22

### Bug Fixes

- Allow crop processor without width or height parameters - fix #34 - ([39af691](https://github.com/ambroisemaupate/intervention-request/commit/39af69160e6dbb891a598398ec33472f01514c34)) - Ambroise Maupate
- update expected MD5 hashes in image crop tests - ([b8408b8](https://github.com/ambroisemaupate/intervention-request/commit/b8408b807b2ed98a7eb725172e03639e42ba19e8)) - Ambroise Maupate

## [6.0.2](https://github.com/ambroisemaupate/intervention-request/compare/v6.0.1...v6.0.2) - 2025-07-01

### Bug Fixes

- update version to 6.0.2 and bump PHP version to 8.3.22 - ([c655393](https://github.com/ambroisemaupate/intervention-request/commit/c65539348e3e0209078091828eba994fe1adb871)) - Ambroise Maupate

## [6.0.1](https://github.com/ambroisemaupate/intervention-request/compare/v6.0.0...v6.0.1) - 2025-07-01

### Bug Fixes

- remove unused flysystem dependencies - ([b0a8662](https://github.com/ambroisemaupate/intervention-request/commit/b0a8662a207a28202e9fe1c6a1cbbff2203871aa)) - Ambroise Maupate

## [6.0.0](https://github.com/ambroisemaupate/intervention-request/compare/v5.1.0...v6.0.0) - 2025-05-27

### Features

- Upgrade to Intervention Image 3.x ([#32](https://github.com/ambroisemaupate/intervention-request/issues/32)) - ([0d27f85](https://github.com/ambroisemaupate/intervention-request/commit/0d27f85c8b732ea204f58c99326276defcd1af36)) - Eliot

## [5.1.0](https://github.com/ambroisemaupate/intervention-request/compare/v5.0.1...v5.1.0) - 2025-04-07

### Bug Fixes

- Changed Rotate and Flip processor priority - ([74534cc](https://github.com/ambroisemaupate/intervention-request/commit/74534cc30d416440dbc46daf1fd330d291404261)) - Ambroise Maupate

### Features

- Add hotspot crop ([#28](https://github.com/ambroisemaupate/intervention-request/issues/28)) - ([f99b533](https://github.com/ambroisemaupate/intervention-request/commit/f99b533cf3885a10f4da90feadbc120e1ca73f0a)) - Eliot

## [5.0.1](https://github.com/ambroisemaupate/intervention-request/compare/v5.0.0...v5.0.1) - 2025-03-29

### Bug Fixes

- Revert to PHP 8.3 to avoid Deprecation messages. Stricter PHP configuration (no sessions, no uploads) - ([32ea982](https://github.com/ambroisemaupate/intervention-request/commit/32ea982c66069fe4e2a4a708ac7e74011aa2f96c)) - Ambroise Maupate

## [5.0.0](https://github.com/ambroisemaupate/intervention-request/compare/v4.1.1...v5.0.0) - 2025-02-27

### Bug Fixes

- Do not check if file is an image in FileCache.php - ([a627625](https://github.com/ambroisemaupate/intervention-request/commit/a627625fd2db2a019172c58bb337c0b6acfd62fd)) - Ambroise Maupate

### Features

- Rewrote Dockerfile for Docker Bake, PHP 8.2 minimum and refactoring - ([e68cf22](https://github.com/ambroisemaupate/intervention-request/commit/e68cf2298e967ddd848928e0b8da39706a0eafa1)) - Ambroise Maupate
- Added StreamNoProcessListener.php to avoid storing native files and image in cache folder - ([28a202a](https://github.com/ambroisemaupate/intervention-request/commit/28a202ab493d0cc367c8607482fbe99ac8b7fd39)) - Ambroise Maupate
- Do not execute PassThroughFileCache when request is marked as stream no-process - ([2be082f](https://github.com/ambroisemaupate/intervention-request/commit/2be082f1063c0b865f77ed47bea9dd3f277cde0a)) - Ambroise Maupate

### Refactor

- Use php-cs-fixer instead of phpcs - ([4fd8e4a](https://github.com/ambroisemaupate/intervention-request/commit/4fd8e4a3d51a71e5d6faa22c7e716ddb3fa41453)) - Ambroise Maupate

## [4.1.0](https://github.com/ambroisemaupate/intervention-request/compare/v4.0.5...v4.1.0) - 2024-09-08

### Features

- Rewrote Dockerfile to build FROM official PHP image instead of Roadiz (removed useless php extensions). Switched to PHP 8.3. - ([c86116c](https://github.com/ambroisemaupate/intervention-request/commit/c86116cd95b2f238810130f9bfafd31c0d816c58)) - Ambroise Maupate

## [4.0.4](https://github.com/ambroisemaupate/intervention-request/compare/v4.0.3...v4.0.4) - 2023-11-13

### Bug Fixes

- Use logger debug instead of info - ([a03f46a](https://github.com/ambroisemaupate/intervention-request/commit/a03f46a002aaf208e8b251382ffbe76f1b29de63)) - Ambroise Maupate

## [4.0.2](https://github.com/ambroisemaupate/intervention-request/compare/v4.0.1...v4.0.2) - 2023-04-26

### Bug Fixes

- Fixes #23 - allow Symfony 6 components - ([62a3c2c](https://github.com/ambroisemaupate/intervention-request/commit/62a3c2c4b44ec2e5851a033f54b72b5decdaedd3)) - Ambroise Maupate

## [4.0.1](https://github.com/ambroisemaupate/intervention-request/compare/v4.0.0...v4.0.1) - 2023-02-10

### Bug Fixes

- Updated README constructor examples - ([531f7db](https://github.com/ambroisemaupate/intervention-request/commit/531f7dbd76d31739a9d5ef0c842d1cc8b2fad9dc)) - Ambroise Maupate

## [4.0.0](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.4...v4.0.0) - 2023-02-10

### ⚠ Breaking changes

- Requires php80 minimum

### Bug Fixes

- Return null if no filesystem is configured - ([4c2147d](https://github.com/ambroisemaupate/intervention-request/commit/4c2147d26a0fc20bead32bfb1a22ed9938f49375)) - Ambroise Maupate

### Features

- Added FileResolverInterface to make native file loading abstract - ([7a86ca0](https://github.com/ambroisemaupate/intervention-request/commit/7a86ca0e0b395b43db836f157449926b0f64458f)) - Ambroise Maupate
- Added Flysystem file resolver - ([1eef862](https://github.com/ambroisemaupate/intervention-request/commit/1eef862640c34aeceb1a1a242d340e53b3ed2aad)) - Ambroise Maupate
- Added Flysystem storage abstract and cache system - ([3e8e583](https://github.com/ambroisemaupate/intervention-request/commit/3e8e583074cce63aabdd667e22332a55ba1bc448)) - Ambroise Maupate
-  [**breaking**]Requires php80 minimum - ([8c74872](https://github.com/ambroisemaupate/intervention-request/commit/8c7487210af7765bd98c21c342d9b56aa2b937a1)) - Ambroise Maupate

## [3.3.4](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.3...v3.3.4) - 2022-11-10

### Bug Fixes

- Wrong type casting on default quality - ([a2a00ec](https://github.com/ambroisemaupate/intervention-request/commit/a2a00ec0d5b5bacd4ec2186813bbf69f6a65fe41)) - Ambroise Maupate

## [3.3.3](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.2...v3.3.3) - 2022-11-09

### Bug Fixes

- Initialize QualitySubscriber with default configured quality - ([a3f344c](https://github.com/ambroisemaupate/intervention-request/commit/a3f344c6635420b4494dfcde78a911391c7b2235)) - Ambroise Maupate

## [3.3.2](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.1...v3.3.2) - 2022-11-09

### Features

- Added default script to build and push Docker image for AMD64 / ARM64 - ([02092db](https://github.com/ambroisemaupate/intervention-request/commit/02092dbc0adc607063b467cafe411a99403ce2ca)) - Ambroise Maupate

## [3.3.1](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.0...v3.3.1) - 2022-09-07

### Bug Fixes

- Allow converting AVIF/HEIC back to webp or jpg - ([29f7843](https://github.com/ambroisemaupate/intervention-request/commit/29f7843b0d5b710d7269a7b774202b4701017416)) - Ambroise Maupate

## [3.3.0](https://github.com/ambroisemaupate/intervention-request/compare/v3.2.3...v3.3.0) - 2022-09-07

### Bug Fixes

- Visibility must be declared on all constants if your project supports PHP 7.1 or later - ([f97ad10](https://github.com/ambroisemaupate/intervention-request/commit/f97ad105dca1931646b071378377100fafd959bb)) - Ambroise Maupate

### Refactor

- Use type hinting and methods return types - ([212c8b5](https://github.com/ambroisemaupate/intervention-request/commit/212c8b56088d327898894d5f761e2d4301d7438c)) - Ambroise Maupate

## [3.2.3](https://github.com/ambroisemaupate/intervention-request/compare/v3.2.2...v3.2.3) - 2022-06-22

### Bug Fixes

- allow using align command with resized crop - ([54d344e](https://github.com/ambroisemaupate/intervention-request/commit/54d344eca2a3aaeef59f6b1f4d48b11a23c8ff48)) - Ambroise Maupate

<!-- generated by git-cliff -->
