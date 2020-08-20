# Bolt CMS Image Extension

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
     heights: [ 300, 600, 840, 1100 ] # Optional. If heights is not set, the height will be relative to the width.
     fit: default # Uses Bolt's `thumbnail` fit options. Pass an array, e.g. [ crop, fit, crop ] to adjust for different widths.
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

Note: In the example above, any config option that is not supplied will be defaulted to the config name `'default'`.


### How to use `responsive_image` with images inside Set fields

If you get the following error message
```
Argument 1 passed to IvoValchev\ImageExtension\Twig\ImageExtension::getResponsiveImage() must be an instance of Bolt\Entity\Field\ImageField or null, array given
```
it probably means that you are trying to pass the image value, rather than the image itself to the `responsive_image` function. This happens most often inside a set.

If you bump into this, update your twig template:

From using a set like this:
```twig
{{ responsive_image(section.photo) }} {# given section is a field of type set #}
```

To this:
```twig
{{ responsive_image(section.value.photo) }} {# note the `.value` #}
```
