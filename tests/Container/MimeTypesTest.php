<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use Tests\DirectiveContainerTests;
use JDZ\HtaccessMaker\Container\MimeTypes;

class MimeTypesTest extends BaseContainerTest
{
    use EmptyContainerTests;
    use DirectiveContainerTests;

    protected string $containerClass = MimeTypes::class;

    public function testMimeTypesWithBasicConfig(): void
    {
        $container = new MimeTypes();
        $container->process([
            'mimeTypes' => [
                'application/json' => '.json',
                'text/css' => '.css',
                'application/javascript' => '.js'
            ]
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('AddType application/json .json', $output);
        $this->assertStringContainsString('AddType text/css .css', $output);
        $this->assertStringContainsString('AddType application/javascript .js', $output);
    }

    public function testMimeTypesWithCustomFormats(): void
    {
        $container = new MimeTypes();
        $container->process([
            'mimeTypes' => [
                'application/x-custom' => '.custom',
                'text/x-template' => '.tpl .template'
            ]
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('AddType application/x-custom .custom', $output);
        $this->assertStringContainsString('AddType text/x-template .tpl .template', $output);
    }

    public function testMimeTypesWithManualDirectives(): void
    {
        $container = new MimeTypes();
        $container->addDirective('AddType text/plain .txt');
        $container->addDirective('AddType image/png .png');

        $output = $container->toString();

        $this->assertStringContainsString('AddType text/plain .txt', $output);
        $this->assertStringContainsString('AddType image/png .png', $output);
    }

    public function testMimeTypesWithComments(): void
    {
        $container = new MimeTypes();
        $container->addDirective('# Web fonts');
        $container->addDirective('AddType font/woff2 .woff2');
        $container->addDirective('# Modern images');
        $container->addDirective('AddType image/webp .webp');

        $output = $container->toString(true);

        $this->assertStringContainsString('# Web fonts', $output);
        $this->assertStringContainsString('# Modern images', $output);
    }

    public function testMimeTypesEmpty(): void
    {
        $container = new MimeTypes();
        $this->assertEquals('', $container->toString());
    }
}
