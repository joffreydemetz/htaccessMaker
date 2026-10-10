<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Container\CspContainer;

class CspContainerTest extends BaseContainerTest
{
    use EmptyContainerTests;
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = CspContainer::class;

    public function testCspContainerWithBasicConfig(): void
    {
        $container = new CspContainer();
        $container->process([
            'enabled' => true,
            'useXContentSecurityPolicy' => false,
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline'",
                'style-src' => "'self' 'unsafe-inline'"
            ]
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('Content-Security-Policy', $output);
        $this->assertStringContainsString("default-src 'self'", $output);
    }

    public function testCspContainerWithXContentSecurityPolicy(): void
    {
        $container = new CspContainer();
        $container->process([
            'enabled' => true,
            'useXContentSecurityPolicy' => true,
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self'"
            ]
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('X-Content-Security-Policy', $output);
        $this->assertStringContainsString("default-src 'self'", $output);
    }

    public function testCspContainerWithComplexPolicy(): void
    {
        $container = new CspContainer();
        $container->process([
            'enabled' => true,
            'useXContentSecurityPolicy' => false,
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' https://cdn.example.com",
                'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com",
                'img-src' => "'self' data: https:",
                'font-src' => "'self' https://fonts.gstatic.com",
                'connect-src' => "'self' https://api.example.com",
                'frame-ancestors' => "'none'"
            ]
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('script-src', $output);
        $this->assertStringContainsString('style-src', $output);
        $this->assertStringContainsString('img-src', $output);
        $this->assertStringContainsString('font-src', $output);
        $this->assertStringContainsString('connect-src', $output);
        $this->assertStringContainsString('frame-ancestors', $output);
    }

    public function testCspContainerWithReportUri(): void
    {
        $container = new CspContainer();
        $container->process([
            'enabled' => true,
            'useXContentSecurityPolicy' => false,
            'csp' => [
                'default-src' => "'self'",
                'report-uri' => '/csp-report-endpoint'
            ]
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('report-uri', $output);
        $this->assertStringContainsString('/csp-report-endpoint', $output);
    }
}
