<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\Container\FilesMatch;

class FilesMatchTest extends ContainerTest
{
    protected string $containerClass = FilesMatch::class;
    protected bool $noDefaultsTest = true;
    protected bool $skipDirectiveTests = true;

    public function testFilesMatchWithPattern(): void
    {
        $container = new FilesMatch();
        $container->process(['pattern' => '\.(css|js)$']);
        $container->addDirective('Header set Cache-Control "public, max-age=31536000"');

        $output = $container->toString();

        $this->assertStringContainsString('<FilesMatch "\.(css|js)$">', $output);
        $this->assertStringContainsString('Header set Cache-Control "public, max-age=31536000"', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }

    public function testFilesMatchWithMultipleDirectives(): void
    {
        $container = new FilesMatch();
        $container->process(['pattern' => '\.(woff|woff2|ttf|eot)$']);
        $container->addDirective('Header set Access-Control-Allow-Origin "*"');
        $container->addDirective('Header set Cache-Control "public, max-age=604800"');

        $output = $container->toString(true);

        $this->assertStringContainsString('<FilesMatch "\.(woff|woff2|ttf|eot)$">', $output);
        $this->assertStringContainsString('Header set Access-Control-Allow-Origin "*"', $output);
        $this->assertStringContainsString('Header set Cache-Control "public, max-age=604800"', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }
}
