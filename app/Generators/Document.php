<?php

namespace Neoground\Imgcoon\Generators;

use Neoground\Imgcoon\GeneratorInterface;

class Document implements GeneratorInterface
{
    /**
     * An array of all mime types (wildcard, str_contains) for files
     * which will be processed with LibreOffice (conversion to PDF)
     *
     * @var array|string[]
     */
    protected static array $libre_office_mime_types = [
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

    public function generate(): bool
    {
        // TODO: Implement generate() method.
        return false;
    }

    public static function isSupported(string $mime): bool
    {
        foreach (self::$libre_office_mime_types as $lomt) {
            if (str_contains($mime, $lomt)) {
                return true;
            }
        }
        return false;
    }
}