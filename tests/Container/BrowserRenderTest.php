<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Container\BrowserRender;

class BrowserRenderTest extends BaseContainerTest
{
    use EmptyContainerTests;
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = BrowserRender::class;

    public function testBrowserRenderWithProcess(): void
    {
        $container = new BrowserRender();
        $container->process(['enabled' => true]);

        $output = $container->toString(true);

        $this->assertStringContainsString('<FilesMatch ".(php|html)$">', $output);
        $this->assertStringContainsString('Content-Style-Type', $output);
        $this->assertStringContainsString('Content-Script-Type', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }

    public function testBrowserRenderWithSecurityHeaders(): void
    {
        $container = new BrowserRender();
        $container->addDirective('Header always set X-Content-Type-Options "nosniff"');
        $container->addDirective('Header always set X-Frame-Options "DENY"');
        $container->addDirective('Header always set X-XSS-Protection "1; mode=block"');

        $output = $container->toString();

        $this->assertStringContainsString('X-Content-Type-Options', $output);
        $this->assertStringContainsString('X-Frame-Options', $output);
        $this->assertStringContainsString('X-XSS-Protection', $output);
    }
}
