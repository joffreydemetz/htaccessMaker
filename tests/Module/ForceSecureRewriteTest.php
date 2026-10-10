<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Module\ForceSecureRewrite;

class ForceSecureRewriteTest extends BaseContainerTest
{
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = ForceSecureRewrite::class;

    public function testForceSecureRewriteBasic(): void
    {
        $container = new ForceSecureRewrite();
        $container->process(['enabled' => true]);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteCond %{HTTPS} off', $output);
        $this->assertStringContainsString('RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI}', $output);
        $this->assertStringContainsString('[R=301,L]', $output);
    }

    public function testForceSecureRewriteWithExcludePaths(): void
    {
        $container = new ForceSecureRewrite();
        $container->process(['excludePaths' => ['/api', '/webhook']]);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteCond %{HTTPS} off', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/api$', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/webhook$', $output);
        $this->assertStringContainsString('RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI}', $output);
    }

    public function testForceSecureRewriteWithSingleExcludePath(): void
    {
        $container = new ForceSecureRewrite();
        $container->process(['excludePaths' => ['/insecure']]);

        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/insecure$', $container->toString());
    }

    public function testForceSecureRewriteWithEmptyExcludePaths(): void
    {
        $container = new ForceSecureRewrite();
        $container->process([
            'excludePaths' => [],
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteCond %{HTTPS} off', $output);
        $this->assertStringContainsString('RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI}', $output);
        // Should not contain any exclusion conditions
        $this->assertStringNotContainsString('!^/', $output);
    }
}
