# Upgrade to v6.0

## Breaking changes

**Intervention Request now requires Intervention Image 3.x.**
This is a major upgrade that includes significant changes to the underlying image processing library.
If your project relies on specific features or behaviors of Intervention Image 2.x, you may need to adjust your code accordingly.

### Signature changes

* All classes and methods depending on `Image` class now depend on `ImageInterface`
* `InterventionRequest` class now requires an implementation of `ImageEncoderInterface` to be passed as a constructor argument.  Ensure that you provide a valid `ImageEncoderInterface` implementation such as our default `ImageEncoder`
* `Configuration::getDriver()` method returns `ImagickDriver|GdDriver` instead of a `string`

## Minor upgrades

### Watermarking Changes

**Watermarking now uses an image instead of a text string.**
You must define the path to the watermark image in your configuration using the new environment variable:

```env
IR_WATERMARK_PATH=/path/to/watermark.png
```

Make sure the file is accessible and in a supported image format (e.g., PNG, JPEG).

### Background Color Parameter Changes

**The `limit_color` query parameter has been removed.**
From now on, only the `background` query parameter should be used to define a background color for the image, i.e.:

```
?background=ffffff
```

Ensure that your client-side code or API consumers are updated accordingly to use only `background`.

