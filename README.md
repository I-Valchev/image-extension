# Bolt CMS Image Extension
ia
Responsive image extension for Bolt CMS using sizes and srcset.

## Installation

To install, run the following command in your Bolt root directory:

```composer require ivovalchev/image-extension```

## Usage

### Simple usage with defaults

Use the extension in any twig template:

```{{ responsive_image(record|image) }}```

### Usage with custom config settings (re-usable)

The extension config is located at ```config/extensions/ivovalchev-imageextension.yaml```.

Example custom configuration:

```
for_blogpost:
     widths: [ 340, 680, 960, 1260 ]
     class: 'blog-image'
     sizes: ["(min-width: 1260px) 1260px", "(min-width: 780px) 680px", "(min-width: 480px) 340px", "100vw"]
```

Then, to use the custom config in twig:

```
{{ responsive_image(myimage, 'for_blogpost') }}
```

### Usage with config settings as params

```
{{ responsive_image(myimage, 'default', {'widths': [400, 500, 600, 700] }) }}
```

Alternatively, using Twig named arguments:

```twig
{{ responsive_image(myimage, options={'widths': [400, 500, 600, 700]}) }}
```

Note: In the example above, any config option that is not supplied will be defauled to the config name `'default'`.
