<?php

declare(strict_types=1);

namespace IvoValchev\ImageExtension\Twig;

use Bolt\Entity\Field\ImageField;
use Bolt\Extension\ExtensionRegistry;
use Bolt\Repository\MediaRepository;
use IvoValchev\ImageExtension\Extension;
use Tightenco\Collect\Support\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    /** @var \Bolt\Twig\ImageExtension */
    private $imageExtension;

    /** @var MediaRepository */
    private $mediaRepository;

    /** @var ExtensionRegistry */
    private $registry;

    /** @var Collection */
    private $config = null;

    public function __construct(\Bolt\Twig\ImageExtension $imageExtension, MediaRepository $mediaRepository, ExtensionRegistry $registry)
    {
        $this->imageExtension = $imageExtension;
        $this->mediaRepository = $mediaRepository;
        $this->registry = $registry;
    }

    /**
     * Register Twig functions.
     */
    public function getFunctions(): array
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFunction('responsive_image', [$this, 'getResponsiveImage'], $safe),
        ];
    }

    public function getResponsiveImage(?Imagefield $image = null, string $configName = 'default', array $options = []): ?string
    {
        if (! $image) {
            return null;
        }

        if ($this->getExtension($image) === 'svg') {
            // We cannot resize SVGs. Let Bolt handle how a regular svg is displayed.
            return $this->imageExtension->showImage($image);
        }

        $config = $this->getConfig($configName)->merge($options);

        $srcset = $this->getSrcset($image, $config);
        $sizes = $this->getSizes($config);

        $src = (string) $image;
        $alt = $image->getValue()['alt'] ?? '';
        $class = $config->get('class', '');

        return sprintf("<img src='%s' alt='%s' class='%s' srcset='%s' sizes='%s' />", $src, $alt, $class, $srcset, $sizes);
    }

    private function getSizes(Collection $config): string
    {
        return implode($config->get('sizes', []), ',');
    }

    private function getSrcset(Imagefield $image, Collection $config): string
    {
        $widths = collect($config->get('widths', []));
        $heights = collect($config->get('heights', []));
        $fits = $config->get('fit', null);
        if (is_iterable($fits)) {
            $fits = collect($fits);
        }

        $lm = $image->getLinkedMedia($this->mediaRepository);
        $location = $lm->getLocation();
        $path = $lm->getPath();

        $srcset = $widths->reduce(function (array $carry, int $width) use ($image, $location, $path, $heights, $fits) {
            // Get height from config, or calculate relative
            $height = $heights->shift() ?? $this->getRelativeHeight($image, $width);

            // Get fit from config (either array or string)
            $fit = is_iterable($fits) ? $fits->shift() : $fits;

            $carry[] = $this->imageExtension->thumbnail($image, $width, $height, $location, $path, $fit) . ' ' . $width . 'w';
            return $carry;
        }, []);

        return implode(',', $srcset);
    }

    private function getRelativeHeight(Imagefield $image, int $width): int
    {
        $lm = $image->getLinkedMedia($this->mediaRepository);
        $originalWidth = $lm->getWidth();
        $originalHeight = $lm->getHeight();

        return (int) ($width * $originalHeight / $originalWidth);
    }

    private function getConfig(string $configName): Collection
    {
        /** @var Extension $extension */
        $extension = $this->registry->getExtension(\IvoValchev\ImageExtension\Extension::class);
        $config = $extension->getConfig();
        $this->config = $config->has($configName) ? $config->get($configName) : $config->get('default', []);

        return collect($this->config);
    }

    private function getExtension(ImageField $image): ?string
    {
        if (! array_key_exists('filename', $image->getValue())) {
            return null;
        }

        return pathinfo($image->getValue()['filename'], PATHINFO_EXTENSION);
    }
}
