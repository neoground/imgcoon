<?php

namespace Neoground\Imgcoon\Generators;

use Neoground\Imgcoon\GeneratorInterface;

class Video implements GeneratorInterface
{

    public function generate(): bool
    {
        // TODO: Implement generate() method.
        return false;
    }

    public static function isSupported(string $mime): bool
    {
        return str_contains($mime, 'video');
    }
}