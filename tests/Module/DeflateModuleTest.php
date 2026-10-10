<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\DeflateModule;

class DeflateModuleTest extends TestCase
{
    public function testWrappedInItsIfModuleByDefault(): void
    {
        $this->assertSame("<IfModule mod_deflate.c>\n  SetOutputFilter DEFLATE\n</IfModule>\n\n", (new DeflateModule())->toString());
    }

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $module = new DeflateModule();
        $module->process($config);

        $this->assertSame($expected, $module->toString());
    }

    public static function processCases(): array
    {
        return [
            'defaults' => [['enabled' => true], implode("\n", [
                '<IfModule mod_deflate.c>',
                '  SetOutputFilter DEFLATE',
                '  AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json',
                '  # For incompatible browsers',
                '  BrowserMatch ^Mozilla/4 gzip-only-text/html',
                '  BrowserMatch ^Mozilla/4\.0[678] no-gzip',
                '  BrowserMatch \bMSIE !no-gzip !gzip-only-text/html',
                '  BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html',
                '  Header append Vary "Accept-Encoding" env=!dont-vary',
                '</IfModule>',
                '',
                '',
            ])],
            'Vary header alone: env=!dont-vary, no bogus vary token (1.0.9)' => [
                ['mimeTypes' => [], 'browserCompatibility' => false],
                "<IfModule mod_deflate.c>\n  SetOutputFilter DEFLATE\n  Header append Vary \"Accept-Encoding\" env=!dont-vary\n</IfModule>\n\n",
            ],
            'custom types, excluded files quoted for the regex' => [
                ['mimeTypes' => ['text/plain', 'image/svg+xml'], 'browserCompatibility' => false, 'fileExclusions' => ['/robots.txt'], 'varyHeader' => false],
                implode("\n", [
                    '<IfModule mod_deflate.c>',
                    '  SetOutputFilter DEFLATE',
                    '  AddOutputFilterByType DEFLATE text/plain image/svg+xml',
                    '  # Do not compress these files',
                    '  SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png|ico|zip|gz|pdf)$ no-gzip',
                    '  SetEnvIfNoCase Request_URI ^\/robots\.txt$ no-gzip dont-vary',
                    '</IfModule>',
                    '',
                    '',
                ]),
            ],
            'empty config adds nothing' => [[], "<IfModule mod_deflate.c>\n  SetOutputFilter DEFLATE\n</IfModule>\n\n"],
        ];
    }
}
