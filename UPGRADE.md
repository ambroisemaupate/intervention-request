# Upgrade to V6

## Functional upgrade

### Watermarking Changes

* **Watermarking now uses an image instead of a text string.**
  You must define the path to the watermark image in your configuration using the new environment variable:

  ```env
  IR_WATERMARK_PATH=/path/to/watermark.png
  ```

  Make sure the file is accessible and in a supported image format (e.g., PNG, JPEG).

### Background Color Parameter Changes

* **The `limit_color` query parameter has been removed.**
  From now on, only the `background` query parameter should be used to define a background color for the image.

  Example:

  ```
  ?background=ffffff
  ```

  Ensure that your client-side code or API consumers are updated accordingly to use only `background`.


## Breaking changes

### Signature Changes

* **The `Image` class now has its own dedicated interface `ImageInterface` and each function with the Image input or output signature now has its own interface.


### Introduction of `ImageEncoderInterface`

* **The `InterventionRequest` class now requires an implementation of `ImageEncoderInterface` to be passed as a constructor argument.**
  Instead of passing `null`, ensure that you provide a valid `ImageEncoder` implementation when initializing the `InterventionRequest`.
