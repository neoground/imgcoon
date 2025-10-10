<?php

namespace Neoground\Imgcoon;

/**
 * Class Generator
 *
 * Generator base class
 */
class Generator implements GeneratorInterface
{
    /** @var string absolute path to the source file */
    protected string $src_path;
    /** @var string mime string of the source file */
    protected string $src_mime;

    /** @var string absolute path to the destination thumbnail file */
    protected string $dest_path;
    /** @var string mime string of the destination thumbnail file */
    protected string $dest_mime;

    /** @var int width / max width of the thumbnail */
    protected int $width = 600;
    /** @var int height / max height of the thumbnail */
    protected int $height = 600;
    /** @var int quality of the thumbnail (0-100) */
    protected int $quality = 75;

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;
        return $this;
    }

    public function setSource(string $src, ?string $src_mime = null): self
    {
        $this->src_path = $src;
        $this->src_mime = $src_mime;
        return $this;
    }

    public function setDestination(string $dest, ?string $dest_mime = null): self
    {
        $this->dest_path = $dest;
        $this->dest_mime = $dest_mime;
        return $this;
    }

    public function generate(): bool
    {
        // Not implemented by default.
        return false;
    }

    public static function isSupported(string $mime): bool
    {
        // Not implemented by default.
        return false;
    }
}