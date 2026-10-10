<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\UaCompatible;

class UaCompatibleTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = UaCompatible::class;

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $container = new UaCompatible();
        $container->process($config);

        $this->assertSame($expected, $container->toString());
    }

    public static function processCases(): array
    {
        return [
            'defaults: header, unset on static files' => [['enabled' => true], implode("\n", [
                'Header set X-UA-Compatible "IE=Edge"',
                '<FilesMatch "\.(js|css|gif|png|jpe?g|pdf|svg|eot|ttf|otf|woff|woff2|ico)$">',
                '  Header unset X-UA-Compatible',
                '</FilesMatch>',
                '',
                '',
                '',
                '',
            ])],
            'custom browsers and extensions' => [['browsers' => 'IE=edge,chrome=1', 'staticFilesExtensions' => ['css', 'js']], implode("\n", [
                'Header set X-UA-Compatible "IE=edge,chrome=1"',
                '<FilesMatch "\.(css|js)$">',
                '  Header unset X-UA-Compatible',
                '</FilesMatch>',
                '',
                '',
                '',
                '',
            ])],
            'not unset on static files' => [['unsetOnStaticFiles' => false], "Header set X-UA-Compatible \"IE=Edge\"\n\n"],
        ];
    }
}
