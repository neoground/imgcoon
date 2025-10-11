<?php

namespace Neoground\Imgcoon\Generators;

use Neoground\Imgcoon\Generator;
use Neoground\Imgcoon\GeneratorInterface;

/**
 * Creating an image from a CAD file via FreeCAD.
 */
class Cad extends Generator implements GeneratorInterface
{

    public function generate(): bool
    {
        // TODO: Implement generate() method.
        return false;
    }

    public static function isSupported(string $mime): bool
    {
        $mimes = [
            'application/acad',
            'application/dxf',
            'application/x-extension-fcstd',
        ];
        return str_contains($mime, 'model/x-') || in_array($mime, $mimes);
    }
}