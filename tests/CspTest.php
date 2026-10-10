<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Csp;

/**
 * Csp is the deprecated shim over JDZ\CspMaker\Policy: addToGroup() and merge() are its
 * own code; the normalisation cases (keywords, ordering, duplicates) pin what the old
 * htaccessMaker API still returns through jdz/cspmaker.
 */
class CspTest extends TestCase
{
    public function testCspCreationWithArray(): void
    {
        $csp = new Csp(['default' => ['self'], 'script' => ['self', 'unsafe-inline']]);

        $this->assertSame("default-src 'self'; script-src 'self' 'unsafe-inline';", (string) $csp);
    }

    public function testEmptyCsp(): void
    {
        $this->assertSame('', (string) new Csp());
    }

    public function testAddToGroupAppends(): void
    {
        $csp = new Csp(['default' => ['self']]);
        $csp->addToGroup('default', ['https://cdn.example.com']);

        $this->assertSame("default-src 'self' https://cdn.example.com;", (string) $csp);
    }

    public function testAddToGroupOverwrite(): void
    {
        $csp = new Csp();
        $csp->addToGroup('default', ['self', 'unsafe-inline']);
        $csp->addToGroup('default', ['self'], true);

        $this->assertSame("default-src 'self';", (string) $csp);
    }

    public function testMergeWithoutOverwrite(): void
    {
        $csp = new Csp(['default' => ['self']]);
        $csp->merge(['default' => ['unsafe-inline'], 'script' => ['self']]);

        $this->assertSame("default-src 'self' 'unsafe-inline'; script-src 'self';", (string) $csp);
    }

    public function testMergeWithOverwrite(): void
    {
        $csp = new Csp(['default' => ['self', 'unsafe-inline']]);
        $csp->merge(['default' => ['self']], true);

        $this->assertSame("default-src 'self';", (string) $csp);
    }

    public function testMergeAcceptsOneSourceAsAString(): void
    {
        $csp = new Csp();
        $csp->merge(['default' => 'self', 'img' => 'data']);

        $this->assertSame("default-src 'self'; img-src data:;", (string) $csp);
    }

    public function testSpecialValueNormalization(): void
    {
        $csp = new Csp();
        $csp->addToGroup('default', ['self', 'data', 'unsafe-inline', 'unsafe-eval']);

        $this->assertSame("default-src 'self' 'unsafe-eval' 'unsafe-inline' data:;", (string) $csp);
    }

    public function testSpecialValueOrdering(): void
    {
        $csp = new Csp();
        $csp->addToGroup('script', ['unsafe-inline', 'https://example.com', 'self', 'data', 'unsafe-eval']);

        $this->assertSame("script-src 'self' 'unsafe-eval' 'unsafe-inline' data: https://example.com;", (string) $csp);
    }

    public function testUniqueValues(): void
    {
        $csp = new Csp();
        $csp->addToGroup('default', ['self', 'self', 'unsafe-inline', 'self']);

        $this->assertSame("default-src 'self' 'unsafe-inline';", (string) $csp);
    }

    public function testNonSourceDirectives(): void
    {
        $csp = new Csp();
        $csp->addToGroup('base-uri', ['self']);
        $csp->addToGroup('form-action', ['self']);
        $csp->addToGroup('frame-ancestors', ['none']);

        $this->assertSame("base-uri 'self'; form-action 'self'; frame-ancestors 'none';", (string) $csp);
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
            'frame' => ['none'],
        ]);

        $this->assertSame(
            "default-src 'self'; connect-src 'self'; font-src 'self' https://fonts.gstatic.com; frame-src 'none'; "
                . "img-src 'self' data: https:; script-src 'self' 'unsafe-inline' https://apis.google.com; "
                . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;",
            (string) $csp
        );
    }
}
