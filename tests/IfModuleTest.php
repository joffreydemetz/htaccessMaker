<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Directive\ServerSignature;

class IfModuleTest extends ContainerTest
{
    protected string $containerClass = IfModule::class;
    protected bool $noCreationTest = true;
    protected bool $noEmptiesTest = true;
    protected bool $noDefaultsTest = true;
    protected bool $skipDirectiveTests = true;

    public function testIfModuleAttributes(): void
    {
        $container = new IfModule('mod_rewrite.c');
        $container->addDirective('RewriteEngine On');
        $container->ensureApacheCompatibility();

        $output = $container->toString(true);

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testIfModuleWithMultipleDirectives(): void
    {
        $container = new IfModule('mod_headers.c');
        $container->addDirective('Header set X-Test "value1"');
        $container->addDirective('Header set X-Another "value2"');

        $output = $container->toString(true);

        $this->assertStringContainsString('Header set X-Test "value1"', $output);
        $this->assertStringContainsString('Header set X-Another "value2"', $output);
    }

    public function testIfModuleWithDirectiveObjects(): void
    {
        $container = new IfModule('mod_mime.c');
        $directive = new ServerSignature('Off');
        $container->addDirective($directive);

        $container->ensureApacheCompatibility();
        $output = $container->toString(true);

        $this->assertStringContainsString('<IfModule mod_mime.c>', $output);
        $this->assertStringContainsString('ServerSignature Off', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testIfModuleEmpty(): void
    {
        $container = new IfModule('mod_empty.c');

        $output = $container->toString(true);

        $this->assertStringNotContainsString('<IfModule mod_empty.c>', $output);
    }

    public function testIfModuleIndentation(): void
    {
        $container = new IfModule('mod_test.c');
        $container->addDirective('TestDirective On');
        $container->ensureApacheCompatibility();

        $output = $container->toString(true, 1);
        $lines = explode("\n", $output);

        // Check that the opening tag is indented
        foreach ($lines as $line) {
            if (strpos($line, '<IfModule mod_test.c>') !== false) {
                $this->assertStringStartsWith('  ', $line);
            }
            if (strpos($line, 'TestDirective On') !== false) {
                $this->assertStringStartsWith('    ', $line);
            }
            if (strpos($line, '</IfModule>') !== false) {
                $this->assertStringStartsWith('  ', $line);
            }
        }
    }

    public function testIfModuleWithoutComments(): void
    {
        $container = new IfModule('mod_test.c');
        $container->addDirective('# This comment should not appear');
        $container->addDirective('Options -Indexes');
        $container->ensureApacheCompatibility();

        $output = $container->toString(false);

        $this->assertStringContainsString('<IfModule mod_test.c>', $output);
        $this->assertStringContainsString('Options -Indexes', $output);
        $this->assertStringContainsString('</IfModule>', $output);
        $this->assertStringNotContainsString('This comment should not appear', $output);
    }

    public function testIfModuleNesting(): void
    {
        $outer = new IfModule('mod_rewrite.c');
        $inner = new IfModule('mod_ssl.c');

        $inner->addDirective('SSLEngine On');
        $outer->addDirective('RewriteEngine On');
        $outer->addDirective($inner);

        $outer->ensureApacheCompatibility();
        $output = $outer->toString(true);

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('<IfModule mod_ssl.c>', $output);
        $this->assertStringContainsString('SSLEngine On', $output);
        $this->assertStringContainsString('</IfModule>', $output);

        // Count the occurrences of </IfModule> - should be 2
        $this->assertEquals(2, substr_count($output, '</IfModule>'));
    }

    public function testIfModuleDifferentModuleNames(): void
    {
        $modules = [
            'mod_rewrite.c',
            'mod_expires.c',
            'mod_deflate.c',
            'mod_ssl.c'
        ];

        foreach ($modules as $module) {
            $container = new IfModule($module);
            $container->addDirective('TestDirective');
            $container->ensureApacheCompatibility();

            $output = $container->toString(true);
            $this->assertStringContainsString("<IfModule $module>", $output);
        }
    }

    public function testFluentInterface(): void
    {
        $container = new IfModule('mod_test.c');
        $result = $container->addDirective('Test1')->addDirective('Test2');

        $this->assertSame($container, $result);

        $output = $container->toString(true);
        $this->assertStringContainsString('Test1', $output);
        $this->assertStringContainsString('Test2', $output);
    }
}
