<?php

namespace Neoground\Imgcoon;

use claviska\SimpleImage;

class Imgcoon
{
    /** @var string generator to use (default: auto) */
    protected string $generator = 'auto';

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

    /** @var string anchor point for cropping */
    protected string $anchor_point = 'center';

    /** @var string image mode: crop (default), bestfit, canvas */
    protected string $mode = 'crop';

    /** @var array available generators */
    protected array $generators = [
        'Audio',
        'Document',
        'Image',
        'Pdf',
        'Video',
    ];

    public static function create(string $src_path,
                                  string $dest_path,
                                  string $dest_mime = 'image/webp',
                                  string $mode = 'crop',
                                  bool   $override = true,
                                  string $generator = 'auto'): bool
    {
        $x = new self();
        $x->setSource($src_path);
        $x->setDestination($dest_path, $dest_mime);
        $x->setMode($mode);
        $x->setGenerator($generator);
        return $x->generate($override);
    }

    /**
     * Set the generator to use.
     *
     * Only changes the generator if a valid one is chosen.
     * By default, this is chosen automatically depending on source's mime.
     *
     * @param string $generator the wanted generator (case-sensitive).
     *
     * @return $this
     */
    public function setGenerator(string $generator): static
    {
        if (in_array($generator, $this->generators)) {
            $this->generator = $generator;
        }
        return $this;
    }

    /**
     * Set the source file
     *
     * @param string $src_path absolute path to the source file
     *
     * @return static
     */
    public function setSource(string $src_path): static
    {
        $this->src_path = $src_path;
        $this->src_mime = mime_content_type($src_path);
        return $this;
    }

    /**
     * Set the destination file path + mime
     *
     * This will be your thumbnail image.
     * Recommended file types: image/jpeg, image/png, image/webp
     *
     * @param string $dest_path the absolute path to your thumbnail image
     * @param string $dest_mime the mime of the thumbnail image
     *
     * @return static
     */
    public function setDestination(string $dest_path, string $dest_mime = 'image/webp'): static
    {
        $this->dest_path = $dest_path;
        $this->dest_mime = $dest_mime;
        return $this;
    }

    /**
     * Set the image mode.
     *
     * Can be:
     *
     * - crop: default, resizes + crops the image to the wanted dimensions
     * - bestfit: best fit the dimensions, keep the aspect ratio of the image
     * - canvas: resize the image so it fits completely in the dimensions (on the canvas)
     *
     * @param string $mode the wanted mode: crop, bestfit, canvas
     *
     * @return static
     */
    public function setMode(string $mode): static
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Set the quality of the thumbnail
     *
     * @param int $quality wanted quality 0-100
     *
     * @return static
     */
    public function setQuality(int $quality): static
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * Generates the image.
     *
     * Make sure source and destination are set.
     * This method automatically detects the type based
     * on the source file's mime.
     *
     * @param bool $override override an existing thumbnail? Default: true.
     *
     * @return bool true on success, false on failure.
     */
    public function generate(bool $override = true): bool
    {
        if (!file_exists($this->src_path)) {
            return false;
        }

        $dir = dirname($this->dest_path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!$override && file_exists($this->dest_path)) {
            return false;
        }

        if (file_exists($this->dest_path)) {
            unlink($this->dest_path);
        }

        // Go through all generators and find a suiting one
        foreach ($this->generators as $class) {
            $fqcn = "Neoground\\Imgcoon\\Generators\\" . $class;

            if (!class_exists($fqcn)) {
                continue;
            }

            try {
                if (($this->generator == 'auto' && $fqcn::isSupported($this->src_mime))
                    || ($this->generator != 'auto' && $this->generator == $class)) {
                    return (new $fqcn())
                        ->setSource($this->src_path, $this->src_mime)
                        ->setDestination($this->dest_path, $this->dest_mime)
                        ->setQuality($this->quality)
                        ->generate();
                }
            } catch (\Throwable) {
                // ignore and try next
            }
        }

        // Invalid type
        return false;
    }

    /**
     * Optimize the thumbnail
     *
     * After a thumbnail is generated with a 3rd party tool,
     * this needs to be optimized (cropped, resized, compressed).
     *
     * @param string|null $src_path optional image source path to use. By default, it is the $dest_path.
     *
     * @return bool true on success, false on error
     */
    private function optimizeThumbnail(string $src_path = null): bool
    {
        if (empty($src_path)) {
            $src_path = $this->dest_path;
        }

        if (file_exists($src_path)) {
            try {
                $img = new SimpleImage();
                $img->fromFile($src_path)
                    ->autoOrient();

                switch ($this->mode) {
                    case 'crop':
                        $img->thumbnail($this->width, $this->height);
                        break;
                    case 'bestfit':
                        $img->bestFit($this->width, $this->height);
                        break;
                    case 'canvas':
                        $img->bestFit($this->width, $this->height);

                        // Detect transparency
                        $hasAlpha = $this->hasTransparency($img, $img->getMimeType());
                        $bgColor = $hasAlpha ? 'transparent' : $this->inferBackgroundColor($img);

                        $canvas = new SimpleImage();
                        $canvas->fromNew($this->width, $this->height, $bgColor);
                        $canvas->overlay($img);
                        $img = $canvas;
                        break;
                }

                $img->toFile($this->dest_path, $this->dest_mime, $this->quality);
            } catch (\Exception $e) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function hasTransparency(SimpleImage $img, string $mime): bool
    {
        if (!in_array($mime, ['image/png', 'image/webp', 'image/gif'])) {
            return false;
        }

        // Sample some pixels for transparency
        $w = $img->getWidth();
        $h = $img->getHeight();
        $samples = [
            [$w / 2, $h / 2],
            [0, 0], [$w - 1, 0],
            [0, $h - 1], [$w - 1, $h - 1],
        ];

        foreach ($samples as [$x, $y]) {
            $color = $img->getColorAt((int)$x, (int)$y);
            if (isset($color['alpha']) && $color['alpha'] < 1.0) {
                return true;
            }
        }

        return false;
    }

    private function inferBackgroundColor(SimpleImage $img): string
    {
        $width = $img->getWidth();
        $height = $img->getHeight();

        $samplePoints = [
            [2, 2], // top-left
            [$width - 2, 2], // top-right
            [2, $height - 2], // bottom-left
            [$width - 2, $height - 2], // bottom-right
            [(int)($width / 2), 2], // top-center
            [(int)($width / 2), $height - 2], // bottom-center
            [2, (int)($height / 2)], // left-center
            [$width - 2, (int)($height / 2)], // right-center
        ];

        $colors = [];

        foreach ($samplePoints as [$x, $y]) {
            $color = $img->getColorAt((int)$x, (int)$y);
            if (isset($color['red'], $color['green'], $color['blue'])) {
                $rgb = [$color['red'], $color['green'], $color['blue']];
                $colors[] = $rgb;
            }
        }

        // Check if majority are close to white (within delta tolerance)
        $whiteCount = 0;
        foreach ($colors as [$r, $g, $b]) {
            if ($r > 240 && $g > 240 && $b > 240 && max($r, $g, $b) - min($r, $g, $b) < 10) {
                $whiteCount++;
            }
        }

        // If most of the edge samples are near white, assume white background
        if ($whiteCount >= (count($colors) * 0.6)) {
            return '#ffffff';
        }

        // Optional: calculate average edge color as fallback
        $avg = [0, 0, 0];
        foreach ($colors as $rgb) {
            $avg[0] += $rgb[0];
            $avg[1] += $rgb[1];
            $avg[2] += $rgb[2];
        }
        $n = count($colors);
        $avg = array_map(fn($v) => (int)round($v / $n), $avg);

        return sprintf("#%02x%02x%02x", ...$avg);
    }
}