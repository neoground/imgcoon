<?php

namespace Neoground\Imgcoon\Generators;

use Neoground\Imgcoon\Generator;
use Neoground\Imgcoon\GeneratorInterface;

/**
 * Creating an image from ebook files via ebook-convert.
 */
class Ebook extends Generator implements GeneratorInterface
{

    public function generate(): bool
    {
        // TODO: Implement generate() method.
        return false;
    }

    public static function isSupported(string $mime): bool
    {
        $mimes = [
            'application/epub+zip',
        ];
        return str_contains($mime, 'ebook') || in_array($mime, $mimes);
    }
}