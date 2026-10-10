<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Module\DeflateModule;

class DeflateModuleTest extends BaseContainerTest
{
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = DeflateModule::class;

    public function testDeflateModuleWithDefaults(): void
    {
        $container = new DeflateModule();
        $container->process(['enabled' => true]);

        $output = $container->toString();

        $this->assertStringContainsString('AddOutputFilterByType DEFLATE text/html', $output);
        $this->assertStringContainsString('BrowserMatch', $output);
    }

    public function testDeflateModuleWithMimeTypes(): void
    {
        $container = new DeflateModule();
        $container->process([
            'mimeTypes' => [
                'text/plain',
                'text/html',
                'application/javascript',
            ],
        ]);

        $output = $container->toString(true);

        $this->assertStringContainsString('AddOutputFilterByType DEFLATE text/plain text/html application/javascript', $output);
    }

    public function testDeflateModuleWithExclusions(): void
    {
        $container = new DeflateModule();
        $container->process([
            'fileExclusions' => ['test.ico'],
        ]);

        $output = $container->toString(true);

        $this->assertStringContainsString('SetEnvIfNoCase Request_URI', $output);
        $this->assertStringContainsString('no-gzip', $output);
    }

    public function testDeflateModuleWithCompressionLevel(): void
    {
        $container = new DeflateModule();
        $container->addDirective('DeflateCompressionLevel 9');
        $container->addDirective('SetOutputFilter DEFLATE');

        $output = $container->toString(true);

        $this->assertStringContainsString('DeflateCompressionLevel 9', $output);
        $this->assertStringContainsString('SetOutputFilter DEFLATE', $output);
    }

    public function testDeflateModuleWithMemoryLevel(): void
    {
        $container = new DeflateModule();
        $container->addDirective('DeflateMemLevel 9');
        $container->addDirective('DeflateWindowSize 15');
        $container->addDirective('SetOutputFilter DEFLATE');

        $output = $container->toString(true);

        $this->assertStringContainsString('DeflateMemLevel 9', $output);
        $this->assertStringContainsString('DeflateWindowSize 15', $output);
    }

    public function testDeflateModuleWithBrowserCompatibility(): void
    {
        $container = new DeflateModule();
        $container->addDirective('SetOutputFilter DEFLATE');
        $container->addDirective('BrowserMatch ^Mozilla/4 gzip-only-text/html');
        $container->addDirective('BrowserMatch ^Mozilla/4\.0[678] no-gzip');
        $container->addDirective('BrowserMatch \\bMSIE !no-gzip !gzip-only-text/html');

        $output = $container->toString(true);

        $this->assertStringContainsString('BrowserMatch ^Mozilla/4 gzip-only-text/html', $output);
        $this->assertStringContainsString('BrowserMatch ^Mozilla/4\.0[678] no-gzip', $output);
        $this->assertStringContainsString('BrowserMatch \\bMSIE !no-gzip !gzip-only-text/html', $output);
    }

    public function testDeflateModuleWithComments(): void
    {
        $container = new DeflateModule();
        $container->addDirective('# Enable compression for text files');
        $container->addDirective('SetOutputFilter DEFLATE');
        $container->addDirective('# Exclude already compressed files');
        $container->addDirective('SetEnvIfNoCase Request_URI \\.(?:gif|jpe?g|png)$ no-gzip');

        $output = $container->toString(true);

        $this->assertStringContainsString('# Enable compression for text files', $output);
        $this->assertStringContainsString('# Exclude already compressed files', $output);
    }

    public function testDeflateModuleWithoutComments(): void
    {
        $container = new DeflateModule();
        $container->addDirective('# This is a comment');
        $container->addDirective('SetOutputFilter DEFLATE');
        $container->addDirective('# Another comment');

        $output = $container->toString(false);

        $this->assertStringNotContainsString('# This is a comment', $output);
        $this->assertStringNotContainsString('# Another comment', $output);
        $this->assertStringContainsString('SetOutputFilter DEFLATE', $output);
    }

    public function testDeflateModuleWithIndentation(): void
    {
        $container = new DeflateModule();
        $container->addDirective('DeflateCompressionLevel 6');

        $output = $container->toString(true, 1);
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            if (strpos($line, '<IfModule') !== false || strpos($line, '</IfModule>') !== false) {
                $this->assertStringStartsWith('  ', $line);
            } elseif (trim($line) !== '') {
                $this->assertStringStartsWith('    ', $line);
            }
        }
    }

    public function testDeflateModuleEmpty(): void
    {
        $container = new DeflateModule();

        $output = $container->toString(true);

        // DeflateModule has ignoreTag=false, so IfModule always renders
        $this->assertStringContainsString('<IfModule mod_deflate.c>', $output);
        $this->assertStringContainsString('SetOutputFilter DEFLATE', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testDeflateModuleFluentInterface(): void
    {
        $container = new DeflateModule();
        $result = $container->addDirective('SetOutputFilter DEFLATE');

        $this->assertSame($container, $result);

        $output = $container->toString(true);
        $this->assertStringContainsString('SetOutputFilter DEFLATE', $output);
    }

    public function testDeflateModuleVaryHeaderIsApacheValid(): void
    {
        // Regression: addVaryHeader() previously emitted the malformed
        // `Header append vary Vary "Accept-Encoding" !dont-vary` which 500'd
        // Apache 2.4. Correct form drops the bogus `vary` token and wraps the
        // condition as env=.
        $container = new DeflateModule();
        $container->process(['varyHeader' => true]);

        $output = $container->toString(true);

        $this->assertStringContainsString('Header append Vary "Accept-Encoding" env=!dont-vary', $output);
        $this->assertStringNotContainsString('append vary', $output);
    }

    public function testDeflateModuleWithFilterProvider(): void
    {
        $container = new DeflateModule();
        $container->addDirective('FilterDeclare COMPRESS');
        $container->addDirective('FilterProvider COMPRESS DEFLATE resp=Content-Type $text/');
        $container->addDirective('FilterChain COMPRESS');

        $output = $container->toString(true);

        $this->assertStringContainsString('FilterDeclare COMPRESS', $output);
        $this->assertStringContainsString('FilterProvider COMPRESS DEFLATE', $output);
        $this->assertStringContainsString('FilterChain COMPRESS', $output);
    }
}
