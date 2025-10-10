<?php

namespace Neoground\Imgcoon;

interface GeneratorInterface
{
    public function generate(): bool;

    public static function isSupported(string $mime): bool;

    public function setWidth(int $width): self;

    public function setHeight(int $height): self;

    public function setQuality(int $quality): self;

    public function setSource(string $src, ?string $src_mime = null): self;

    public function setDestination(string $dest, ?string $dest_mime = null): self;
}