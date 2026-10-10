<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\MimeTypes;

class MimeTypesTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = MimeTypes::class;

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $container = new MimeTypes();
        $container->process($config);

        $this->assertSame($expected, $container->toString());
    }

    public static function processCases(): array
    {
        return [
            'enabled alone: the common web types' => [['enabled' => true], implode("\n", [
                'AddType text/css .css',
                'AddType text/javascript .js',
                'AddType application/javascript .js',
                'AddType application/json .json',
                'AddType application/xml .xml',
                'AddType text/xml .xml',
                'AddType image/svg+xml .svg .svgz',
                'AddType image/x-icon .ico',
                'AddType text/plain .txt',
                'AddType font/woff .woff',
                'AddType font/woff2 .woff2',
                'AddType font/ttf .ttf',
                'AddType font/otf .otf',
                'AddType font/eot .eot',
                '',
                '',
            ])],
            'type => extension(s) as a string' => [
                ['mimeTypes' => ['application/x-custom' => '.custom', 'text/x-template' => '.tpl .template']],
                "AddType application/x-custom .custom\nAddType text/x-template .tpl .template\n\n",
            ],
            'list of type + extensions, an empty one skipped' => [
                ['mimeTypes' => [['type' => 'font/woff2', 'extensions' => ['.woff2']], ['type' => 'font/otf', 'extensions' => []]]],
                "AddType font/woff2 .woff2\n\n",
            ],
            'type => extensions array, then images and documents' => [
                ['mimeTypes' => ['application/manifest+json' => ['.webmanifest']], 'includeImages' => true, 'includeDocuments' => true],
                implode("\n", [
                    'AddType application/manifest+json .webmanifest',
                    'AddType image/jpeg .jpg .jpeg',
                    'AddType image/png .png',
                    'AddType image/gif .gif',
                    'AddType image/webp .webp',
                    'AddType image/avif .avif',
                    'AddType image/bmp .bmp',
                    'AddType image/tiff .tiff .tif',
                    'AddType image/x-icon .ico',
                    'AddType application/pdf .pdf',
                    'AddType application/msword .doc',
                    'AddType application/vnd.openxmlformats-officedocument.wordprocessingml.document .docx',
                    'AddType application/vnd.ms-excel .xls',
                    'AddType application/vnd.openxmlformats-officedocument.spreadsheetml.sheet .xlsx',
                    'AddType application/vnd.ms-powerpoint .ppt',
                    'AddType application/vnd.openxmlformats-officedocument.presentationml.presentation .pptx',
                    '',
                    '',
                ]),
            ],
        ];
    }
}
