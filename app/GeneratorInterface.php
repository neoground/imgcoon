<?php

namespace Neoground\Imgcoon;

interface GeneratorInterface
{
    public function generate(): bool;
    public static function isSupported(string $mime): bool;
}