<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\Module\ExpiresModule;

class ExpiresModuleTest extends ContainerTest
{
    public function testExpiresModuleCreation(): void
    {
        $container = new ExpiresModule();
        $this->assertInstanceOf(ExpiresModule::class, $container);
    }

    public function testExpiresModuleWithBasicExpires(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresDefault "access plus 1 month"');
        $container->ensureApacheCompatibility();

        $output = $container->toString(true);

        $this->assertStringContainsString('<IfModule mod_expires.c>', $output);
        $this->assertStringContainsString('ExpiresActive On', $output);
        $this->assertStringContainsString('ExpiresDefault "access plus 1 month"', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testExpiresModuleWithFileTypeExpires(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresByType text/css "access plus 1 year"');
        $container->addDirective('ExpiresByType application/javascript "access plus 1 year"');
        $container->addDirective('ExpiresByType image/png "access plus 1 month"');
        $container->addDirective('ExpiresByType image/jpg "access plus 1 month"');
        $container->addDirective('ExpiresByType image/jpeg "access plus 1 month"');
        $container->addDirective('ExpiresByType image/gif "access plus 1 month"');

        $output = $container->toString(true);

        $this->assertStringContainsString('ExpiresByType text/css "access plus 1 year"', $output);
        $this->assertStringContainsString('ExpiresByType application/javascript "access plus 1 year"', $output);
        $this->assertStringContainsString('ExpiresByType image/png "access plus 1 month"', $output);
        $this->assertStringContainsString('ExpiresByType image/jpeg "access plus 1 month"', $output);
    }

    public function testExpiresModuleWithVariousTimeFormats(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresByType text/html "access plus 1 hour"');
        $container->addDirective('ExpiresByType text/plain "access plus 1 day"');
        $container->addDirective('ExpiresByType application/pdf "access plus 1 week"');
        $container->addDirective('ExpiresByType video/mp4 "access plus 6 months"');

        $output = $container->toString(true);

        $this->assertStringContainsString('"access plus 1 hour"', $output);
        $this->assertStringContainsString('"access plus 1 day"', $output);
        $this->assertStringContainsString('"access plus 1 week"', $output);
        $this->assertStringContainsString('"access plus 6 months"', $output);
    }

    public function testExpiresModuleWithModificationTime(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresByType text/html "modification plus 2 hours"');
        $container->addDirective('ExpiresByType application/json "modification plus 1 day"');

        $output = $container->toString(true);

        $this->assertStringContainsString('"modification plus 2 hours"', $output);
        $this->assertStringContainsString('"modification plus 1 day"', $output);
    }

    public function testExpiresModuleWithNowTime(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresByType text/css "now plus 1 year"');
        $container->addDirective('ExpiresByType application/javascript "now plus 1 year"');

        $output = $container->toString(true);

        $this->assertStringContainsString('"now plus 1 year"', $output);
    }

    public function testExpiresModuleWithFontFiles(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresByType application/font-woff "access plus 1 year"');
        $container->addDirective('ExpiresByType application/font-woff2 "access plus 1 year"');
        $container->addDirective('ExpiresByType application/vnd.ms-fontobject "access plus 1 year"');
        $container->addDirective('ExpiresByType font/truetype "access plus 1 year"');
        $container->addDirective('ExpiresByType font/opentype "access plus 1 year"');

        $output = $container->toString(true);

        $this->assertStringContainsString('ExpiresByType application/font-woff', $output);
        $this->assertStringContainsString('ExpiresByType font/truetype', $output);
    }

    public function testExpiresModuleWithComments(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('# Enable expires headers');
        $container->addDirective('ExpiresActive On');
        $container->addDirective('# Set default expiration');
        $container->addDirective('ExpiresDefault "access plus 1 week"');
        $container->addDirective('# CSS and JS files');
        $container->addDirective('ExpiresByType text/css "access plus 1 year"');

        $output = $container->toString(true);

        $this->assertStringContainsString('# Enable expires headers', $output);
        $this->assertStringContainsString('# Set default expiration', $output);
        $this->assertStringContainsString('# CSS and JS files', $output);
    }

    public function testExpiresModuleWithoutComments(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('# This is a comment');
        $container->addDirective('ExpiresActive On');
        $container->addDirective('# Another comment');
        $container->addDirective('ExpiresDefault "access plus 1 month"');

        $output = $container->toString(false);

        $this->assertStringNotContainsString('# This is a comment', $output);
        $this->assertStringNotContainsString('# Another comment', $output);
        $this->assertStringContainsString('ExpiresActive On', $output);
        $this->assertStringContainsString('ExpiresDefault "access plus 1 month"', $output);
    }

    public function testExpiresModuleWithIndentation(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresDefault "access plus 1 month"');
        $container->ensureApacheCompatibility();

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

    public function testExpiresModuleEmpty(): void
    {
        $container = new ExpiresModule();
        $container->ensureApacheCompatibility();

        $output = $container->toString(true);

        $this->assertStringContainsString('<IfModule mod_expires.c>', $output);
        $this->assertStringContainsString('ExpiresActive On', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testExpiresModuleEmitsIfModuleWrapperByDefault(): void
    {
        // Regression: ExpiresModule must default to ignoreTag=false (like DeflateModule)
        // so the <IfModule mod_expires.c> wrapper is emitted WITHOUT an explicit
        // ensureApacheCompatibility() call. Without it, bare ExpiresActive directives
        // 500 on Apache servers lacking mod_expires.
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');

        $output = $container->toString(true);

        $this->assertStringContainsString('<IfModule mod_expires.c>', $output);
        $this->assertStringContainsString('ExpiresActive On', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testExpiresModuleFluentInterface(): void
    {
        $container = new ExpiresModule();
        $result = $container->addDirective('ExpiresActive On');

        $this->assertSame($container, $result);

        $output = $container->toString(true);
        $this->assertStringContainsString('ExpiresActive On', $output);
    }

    public function testExpiresModuleWithArchiveFiles(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresByType application/zip "access plus 1 month"');
        $container->addDirective('ExpiresByType application/x-gzip "access plus 1 month"');
        $container->addDirective('ExpiresByType application/x-tar "access plus 1 month"');

        $output = $container->toString(true);

        $this->assertStringContainsString('ExpiresByType application/zip', $output);
        $this->assertStringContainsString('ExpiresByType application/x-gzip', $output);
    }

    public function testExpiresModuleWithMediaFiles(): void
    {
        $container = new ExpiresModule();
        $container->addDirective('ExpiresActive On');
        $container->addDirective('ExpiresByType audio/mpeg "access plus 1 month"');
        $container->addDirective('ExpiresByType video/mp4 "access plus 1 month"');
        $container->addDirective('ExpiresByType video/webm "access plus 1 month"');

        $output = $container->toString(true);

        $this->assertStringContainsString('ExpiresByType audio/mpeg', $output);
        $this->assertStringContainsString('ExpiresByType video/mp4', $output);
        $this->assertStringContainsString('ExpiresByType video/webm', $output);
    }
}
