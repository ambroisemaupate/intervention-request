## [v4.0.1](https://github.com/ambroisemaupate/intervention-request/compare/v4.0.0...v) (2023-02-10)

### Bug Fixes

* Updated README constructor examples ([531f7db](https://github.com/ambroisemaupate/intervention-request/commit/531f7dbd76d31739a9d5ef0c842d1cc8b2fad9dc))

## [v4.0.0](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.4...v) (2023-02-10)

### ⚠ BREAKING CHANGES

* Requires php80 minimum
* `InterventionRequest` constructor signature changed:
    It requires now 2 objects:

    - AM\InterventionRequest\Configuration
    - AM\InterventionRequest\FileResolverInterface

### Features

* Added FileResolverInterface to make native file loading abstract ([7a86ca0](https://github.com/ambroisemaupate/intervention-request/commit/7a86ca0e0b395b43db836f157449926b0f64458f))
* Added Flysystem file resolver ([1eef862](https://github.com/ambroisemaupate/intervention-request/commit/1eef862640c34aeceb1a1a242d340e53b3ed2aad))
* Added Flysystem storage abstract and cache system ([3e8e583](https://github.com/ambroisemaupate/intervention-request/commit/3e8e583074cce63aabdd667e22332a55ba1bc448))
* Requires php80 minimum ([8c74872](https://github.com/ambroisemaupate/intervention-request/commit/8c7487210af7765bd98c21c342d9b56aa2b937a1))

### Bug Fixes

* Return null if no filesystem is configured ([4c2147d](https://github.com/ambroisemaupate/intervention-request/commit/4c2147d26a0fc20bead32bfb1a22ed9938f49375))

## [v3.3.4](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.3...v3.3.4) (2022-11-10)

### Bug Fixes

* Wrong type casting on default quality ([a2a00ec](https://github.com/ambroisemaupate/intervention-request/commit/a2a00ec0d5b5bacd4ec2186813bbf69f6a65fe41))

## [v3.3.3](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.2...v3.3.3) (2022-11-09)

### Bug Fixes

* Initialize QualitySubscriber with default configured quality ([a3f344c](https://github.com/ambroisemaupate/intervention-request/commit/a3f344c6635420b4494dfcde78a911391c7b2235))

## [v3.3.2](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.1...v3.3.2) (2022-11-09)

### Features

* Added default script to build and push Docker image for AMD64 / ARM64 ([02092db](https://github.com/ambroisemaupate/intervention-request/commit/02092dbc0adc607063b467cafe411a99403ce2ca))

## [v3.3.1](https://github.com/ambroisemaupate/intervention-request/compare/v3.3.0...v3.3.1) (2022-09-07)

### Bug Fixes

* Allow converting AVIF/HEIC back to webp or jpg ([29f7843](https://github.com/ambroisemaupate/intervention-request/commit/29f7843b0d5b710d7269a7b774202b4701017416))

## [v3.3.0](https://github.com/ambroisemaupate/intervention-request/compare/v3.2.3...v3.3.0) (2022-09-07)

* Added support for next files formats when using Imagick driver: AVIF, HEIF/HEIC

### Bug Fixes

* Visibility must be declared on all constants if your project supports PHP 7.1 or later ([f97ad10](https://github.com/ambroisemaupate/intervention-request/commit/f97ad105dca1931646b071378377100fafd959bb))

## [v3.2.3](https://github.com/ambroisemaupate/intervention-request/compare/v3.2.2...v3.2.3) (2022-06-22)

### Bug Fixes

* Allow using `align` command with a resized `crop` ([54d344e](https://github.com/ambroisemaupate/intervention-request/commit/54d344eca2a3aaeef59f6b1f4d48b11a23c8ff48))

