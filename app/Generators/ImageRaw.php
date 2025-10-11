<?php

namespace Neoground\Imgcoon\Generators;

use Neoground\Imgcoon\Generator;
use Neoground\Imgcoon\GeneratorInterface;

/**
 * Creating an image from a raw image (e.g., CR2, DNG).
 */
class ImageRaw extends Generator implements GeneratorInterface
{

    public function generate(): bool
    {
        // TODO: Implement generate() method.
        return false;
    }

    public static function isSupported(string $mime): bool
    {
        return str_contains($mime, 'image/x-');
    }
}