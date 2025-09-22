<?php

namespace Neoground\Imgcoon;

class Imgcoon
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
    /** @var string anchor point for cropping */
    protected string $anchor_point = 'center';

    /** @var string image mode: crop (default), bestfit, canvas */
    protected string $mode = 'crop';

    /**
     * An array of all mime types (wildcard, str_contains) for files
     * which will be processed with LibreOffice (conversion to PDF)
     *
     * @var array|string[]
     */
    protected array $libre_office_mime_types = [
        // Modern standard
        'application/vnd.oasis.opendocument', // open docs, e.g. .odt / .ods / .odp / .odc / .odi / .odf
        'application/vnd.openxmlformats-officedocument', // office, e.g. .docx / .xlsx / .pptx

        // Classic files
        'application/rtf', // .rtf
        'text/plain', // .txt
        'text/csv', // .csv

        // Legacy formats
        'application/vnd.sun.xml', // old openoffice, e.g. .sx* / .st*
        'application/vnd.lotus-wordpro', // .lwp
        'application/wordperfect', // .wpd
        'application/x-staroffice', // StarOffice formats
        'application/msword', // .doc
        'application/vnd.ms-word', // .docm
        'application/vnd.ms-excel', // .xls / .xlsm
        'application/vnd.ms-powerpoint', // .ppt
    ];

    public static function create(string $src_path,
                                  string $dest_path,
                                  string $dest_mime = 'image/webp',
                                  string $mode = 'crop',
                                  bool   $override = true): bool
    {
        $x = new self();
        $x->setSource($src_path);
        $x->setDestination($dest_path, $dest_mime);
        $x->setMode($mode);
        return $x->generate($override);
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

        // TODO Add generate methods in own classes with common interface
        if (str_contains($this->src_mime, 'video')) {
            return $this->generateVideoThumbnail();
        }

        if (str_contains($this->src_mime, 'image')) {
            return $this->generateImageThumbnail();
        }

        if (str_contains($this->src_mime, 'application/pdf')) {
            return $this->generatePdfThumbnail();
        }

        if (str_contains($this->src_mime, 'audio')) {
            return $this->generateAudioThumbnail();
        }

        foreach ($this->libre_office_mime_types as $lomt) {
            if (str_contains($this->src_mime, $lomt)) {
                return $this->generateOfficeThumbnail();
            }
        }

        // Invalid type
        return false;
    }
}