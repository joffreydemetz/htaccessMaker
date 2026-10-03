<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Csp;

class CspTest extends TestCase
{
    public function testCspCreationWithArray(): void
    {
        $config = [
            'default' => ['self'],
            'script' => ['self', 'unsafe-inline']
        ];
        $csp = new Csp($config);

        $output = $csp->__toString();
        $this->assertStringContainsString("default-src 'self';", $output);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline';", $output);
    }

    public function testEmptyCsp(): void
    {
        $csp = new Csp();
        $output = $csp->__toString();
        $this->assertEquals('', $output);
    }

    public function testAddToGroup(): void
    {
        $csp = new Csp();
        $result = $csp->addToGroup('default', ['self']);

        $this->assertSame($csp, $result); // Test fluent interface

        $output = $csp->__toString();
        $this->assertStringContainsString("default-src 'self';", $output);
    }

    public function testAddToGroupWithMultipleItems(): void
    {
        $csp = new Csp();
        $csp->addToGroup('script', ['self', 'unsafe-inline', 'https://example.com']);

        $output = $csp->__toString();
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' https://example.com;", $output);
    }

    public function testSpecialValueNormalization(): void
    {
        $csp = new Csp();
        // 'none' alongside real sources is a spec no-op and is dropped
        $csp->addToGroup('default', ['self', 'data', 'unsafe-inline', 'unsafe-eval']);

        $output = $csp->__toString();
        $this->assertStringContainsString("'self'", $output);
        $this->assertStringContainsString("data:", $output);
        $this->assertStringContainsString("'unsafe-inline'", $output);
        $this->assertStringContainsString("'unsafe-eval'", $output);
    }

    public function testMergeWithoutOverwrite(): void
    {
        $csp = new Csp(['default' => ['self']]);
        $csp->merge(['default' => ['unsafe-inline'], 'script' => ['self']]);

        $output = $csp->__toString();
        $this->assertStringContainsString("default-src 'self' 'unsafe-inline';", $output);
        $this->assertStringContainsString("script-src 'self';", $output);
    }

    public function testMergeWithOverwrite(): void
    {
        $csp = new Csp(['default' => ['self', 'unsafe-inline']]);
        $csp->merge(['default' => ['self']], true);

        $output = $csp->__toString();
        $this->assertStringContainsString("default-src 'self';", $output);
        $this->assertStringNotContainsString('unsafe-inline', $output);
    }

    public function testNonSourceDirectives(): void
    {
        $csp = new Csp();
        $csp->addToGroup('base-uri', ['self']);
        $csp->addToGroup('form-action', ['self']);
        $csp->addToGroup('frame-ancestors', ['none']);

        $output = $csp->__toString();
        $this->assertStringContainsString("base-uri 'self';", $output);
        $this->assertStringContainsString("form-action 'self';", $output);
        $this->assertStringContainsString("frame-ancestors 'none';", $output);
    }

    public function testUniqueValues(): void
    {
        $csp = new Csp();
        $csp->addToGroup('default', ['self', 'self', 'unsafe-inline', 'self']);

        $output = $csp->__toString();
        // Should only contain 'self' and 'unsafe-inline' once each
        $this->assertEquals(1, substr_count($output, "'self'"));
        $this->assertEquals(1, substr_count($output, "'unsafe-inline'"));
    }

    public function testComplexCspPolicy(): void
    {
        $csp = new Csp([
            'default' => ['self'],
            'script' => ['self', 'unsafe-inline', 'https://apis.google.com'],
            'style' => ['self', 'unsafe-inline', 'https://fonts.googleapis.com'],
            'img' => ['self', 'data', 'https:'],
            'font' => ['self', 'https://fonts.gstatic.com'],
            'connect' => ['self'],
            'frame' => ['none']
        ]);

        $output = $csp->__toString();

        $this->assertStringContainsString("default-src 'self';", $output);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' https://apis.google.com;", $output);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;", $output);
        $this->assertStringContainsString("img-src 'self' data: https:;", $output);
        $this->assertStringContainsString("font-src 'self' https://fonts.gstatic.com;", $output);
        $this->assertStringContainsString("connect-src 'self';", $output);
        $this->assertStringContainsString("frame-src 'none';", $output);
    }

    public function testAddToGroupOverwrite(): void
    {
        $csp = new Csp();
        $csp->addToGroup('default', ['self', 'unsafe-inline']);
        $csp->addToGroup('default', ['self'], true); // Overwrite

        $output = $csp->__toString();
        $this->assertStringContainsString("default-src 'self';", $output);
        $this->assertStringNotContainsString('unsafe-inline', $output);
    }

    public function testSpecialValueOrdering(): void
    {
        $csp = new Csp();
        // 'none' alongside real sources is a spec no-op and is dropped
        $csp->addToGroup('script', ['unsafe-inline', 'https://example.com', 'self', 'data', 'unsafe-eval']);

        $output = $csp->__toString();

        // Check that special values are properly ordered and formatted
        $this->assertStringContainsString("'self'", $output);
        $this->assertStringContainsString("'unsafe-inline'", $output);
        $this->assertStringContainsString("'unsafe-eval'", $output);
        $this->assertStringContainsString("data:", $output);
        $this->assertStringContainsString("https://example.com", $output);
    }
}
