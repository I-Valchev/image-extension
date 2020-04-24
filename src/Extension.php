<?php

declare(strict_types=1);

namespace IvoValchev\ImageExtension;

use Bolt\Extension\BaseExtension;

class Extension extends BaseExtension
{
    public function getName(): string
    {
        return 'Image Extension';
    }
}
