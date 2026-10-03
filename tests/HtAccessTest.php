<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\Directive\ServerSignature;
use JDZ\HtaccessMaker\Directive\DirectoryIndex;
use JDZ\HtaccessMaker\Container\AntiXSS;
use JDZ\HtaccessMaker\Module\BasicAuthModule;
use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Module\DeflateModule;

class HtAccessTest extends TestCase
{
    public function testWithCommentsEnabledByDefault(): void
    {
        // Comments should be enabled by default
        $htaccess = new HtAccess();
        $htaccess->addDirective(new Comment('Default comment behavior'));
        $htaccess->addDirective('# String comment');

        $output = $htaccess->toString();

        $this->assertStringContainsString('# Default comment behavior', $output);
        $this->assertStringContainsString('# String comment', $output);
    }

    public function testWithCommentsEnabled(): void
    {
        $htaccess = new HtAccess();
        $htaccess->withComments(true);
        $htaccess->addDirective(new Comment('Test comment'));
        $htaccess->addDirective('# String comment');
        $htaccess->addDirective(new ServerSignature('Off'));

        $output = $htaccess->toString();

        $this->assertStringContainsString('# Test comment', $output);
        $this->assertStringContainsString('# String comment', $output);
        $this->assertStringContainsString('ServerSignature Off', $output);
    }

    public function testWithoutComments(): void
    {
        $htaccess = new HtAccess();
        $htaccess->withComments(false);
        $htaccess->addDirective(new Comment('This should not appear'));
        $htaccess->addDirective('# This should also not appear');
        $htaccess->addDirective(new ServerSignature('Off'));

        $output = $htaccess->toString();

        $this->assertStringNotContainsString('This should not appear', $output);
        $this->assertStringNotContainsString('This should also not appear', $output);
        $this->assertStringContainsString('ServerSignature Off', $output);
    }

    // HtAccess       by default ignores Apache compatibility
    // IfModule       by default ignores Apache compatibility
    // DeflateModule  by default forces  Apache compatibility
    public function testWithApacheCompatibilityDisabledByDefault(): void
    {
        $htaccess = new HtAccess();

        $container = new IfModule('mod_test.c');
        $container->addDirective('IfModule mod_test.c should not be visible');
        $htaccess->addDirective($container);

        $container = new DeflateModule();
        $container->addDirective('IfModule mod_deflate.c should be visible');
        $htaccess->addDirective($container);

        $output = $htaccess->toString();

        $this->assertStringNotContainsString('<IfModule mod_test.c>', $output);
        $this->assertStringContainsString('<IfModule mod_deflate.c>', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testWithApacheCompatibilityDisabled(): void
    {
        $htaccess = new HtAccess();
        $htaccess->withApacheCompatibility(false);

        $container = new IfModule('mod_test.c');
        $container->addDirective('IfModule mod_test.c should not be visible');
        $htaccess->addDirective($container);

        $container = new DeflateModule();
        $container->addDirective('IfModule mod_deflate.c should be visible');
        $htaccess->addDirective($container);

        $output = $htaccess->toString();

        $this->assertStringNotContainsString('<IfModule mod_test.c>', $output);
        $this->assertStringContainsString('<IfModule mod_deflate.c>', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testWithApacheCompatibilityEnabled(): void
    {
        $htaccess = new HtAccess();
        $htaccess->withApacheCompatibility(true);

        $container = new IfModule('mod_test.c');
        $container->addDirective('IfModule mod_test.c should not be visible');
        $htaccess->addDirective($container);

        $container = new DeflateModule();
        $container->addDirective('IfModule mod_deflate.c should be visible');
        $htaccess->addDirective($container);

        $output = $htaccess->toString();

        $this->assertStringContainsString('<IfModule mod_test.c>', $output);
        $this->assertStringContainsString('<IfModule mod_deflate.c>', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testNoMoreThanTwoConsecutiveNewlines(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective("Line1\n\n\n\n\nLine2");
        $htaccess->addDirective(new Comment('Comment with spacing'));

        $output = $htaccess->toString();

        // Should not contain more than 2 consecutive newlines
        $this->assertStringNotContainsString("\n\n\n", $output);
    }

    public function testExcessiveNewlinesCleanup(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective("Options -Indexes\n\n\n\n\nServerSignature Off");

        $output = $htaccess->toString();

        // Test that excessive newlines are cleaned up
        $lines = explode("\n", $output);
        $consecutiveEmpty = 0;
        $maxConsecutiveEmpty = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $consecutiveEmpty++;
                $maxConsecutiveEmpty = max($maxConsecutiveEmpty, $consecutiveEmpty);
            } else {
                $consecutiveEmpty = 0;
            }
        }

        $this->assertLessThanOrEqual(2, $maxConsecutiveEmpty);
    }

    public function testNoEmptyDirectivesRendered(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective('');
        $htaccess->addDirective('   ');
        $htaccess->addDirective("\n");
        $htaccess->addDirective(new Comment(''));
        $htaccess->addDirective('ServerSignature Off');

        $output = $htaccess->toString();

        // Should only contain the non-empty directive
        $nonEmptyLines = array_filter(explode("\n", $output), fn($line) => trim($line) !== '');
        $this->assertGreaterThan(0, count($nonEmptyLines));
        $this->assertStringContainsString('ServerSignature Off', $output);
    }

    public function testSkipEmptyStringDirectives(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective('ServerSignature Off');
        $htaccess->addDirective('');
        $htaccess->addDirective('Options -Indexes');

        $output = $htaccess->toString();

        $this->assertStringContainsString('ServerSignature Off', $output);
        $this->assertStringContainsString('Options -Indexes', $output);

        // Should not have extra empty lines from empty directive
        $this->assertStringNotContainsString("Off\n\nOptions", $output);
    }

    public function testStringifyDirective(): void
    {
        $directive = new ServerSignature('Off');
        $result = HtAccess::stringifyDirective($directive, true, true, 0);

        $this->assertEquals('ServerSignature Off', $result);
    }

    public function testStringifyDirectiveWithComments(): void
    {
        $comment = new Comment('Test comment');
        $result = HtAccess::stringifyDirective($comment, true, true, 0);

        $this->assertEquals('# Test comment', $result);
    }

    public function testStringifyDirectiveWithoutComments(): void
    {
        $comment = new Comment('Test comment');
        $result = HtAccess::stringifyDirective($comment, false, true, 0);

        $this->assertEquals('', $result);
    }

    public function testStringifyDirectiveWithStringDirective(): void
    {
        $result = HtAccess::stringifyDirective('ServerSignature Off');
        $this->assertEquals('ServerSignature Off', $result);
    }

    public function testStringifyDirectiveWithStringComment(): void
    {
        $result = HtAccess::stringifyDirective('# Test comment', true);
        $this->assertEquals('# Test comment', $result);
    }

    public function testStringifyDirectiveWithStringCommentDisabled(): void
    {
        $result = HtAccess::stringifyDirective('# Test comment', false);
        $this->assertEquals('', $result);
    }

    public function testFluentInterface(): void
    {
        $htaccess = new HtAccess();
        $result = $htaccess
            ->withComments(true)
            ->withApacheCompatibility(true)
            ->addDirective('ServerSignature Off')
            ->addDirective(new Comment('Test fluent interface'));

        $this->assertSame($htaccess, $result);
        $this->assertInstanceOf(HtAccess::class, $result);

        $output = $result->toString();
        $this->assertStringContainsString('ServerSignature Off', $output);
        $this->assertStringContainsString('# Test fluent interface', $output);
    }

    public function testFluentInterfaceChaining(): void
    {
        $htaccess = new HtAccess();
        $output = $htaccess
            ->withComments(true)
            ->withApacheCompatibility(false)
            ->addDirective(new ServerSignature('Off'))
            ->addDirective(new DirectoryIndex('index.php'))
            ->toString();

        $this->assertStringContainsString('ServerSignature Off', $output);
        $this->assertStringContainsString('DirectoryIndex index.php', $output);
    }

    public function testFluentInterfaceMethodReturn(): void
    {
        $htaccess = new HtAccess();
        $withComments = $htaccess->withComments(true);
        $withCompatibility = $htaccess->withApacheCompatibility(true);
        $withDirective = $htaccess->addDirective('Test');

        $this->assertSame($htaccess, $withComments);
        $this->assertSame($htaccess, $withCompatibility);
        $this->assertSame($htaccess, $withDirective);
    }

    public function testFluentInterfaceComplexChaining(): void
    {
        $htaccess = new HtAccess();
        $result = $htaccess
            ->withComments(false)
            ->addDirective('Options -Indexes')
            ->withComments(true)
            ->addDirective(new Comment('Now with comments'))
            ->withApacheCompatibility(true);

        $this->assertSame($htaccess, $result);

        $output = $result->toString();
        $this->assertStringContainsString('Options -Indexes', $output);
        $this->assertStringContainsString('# Now with comments', $output);
    }

    public function testComplexConfiguration(): void
    {
        $htaccess = new HtAccess();
        $htaccess
            ->withComments(true)
            ->withApacheCompatibility(true)
            ->addDirective(new Comment('Security Configuration'))
            ->addDirective(new ServerSignature('Off'));

        $container = new AntiXSS();
        $container->process([
            'enabled' => true,
            'xssProtection' => '1; mode=block',
            'frameOptions' => 'SAMEORIGIN',
            'contentTypeOptions' => 'nosniff'
        ]);

        $htaccess->addDirective($container);

        $output = $htaccess->toString();

        $this->assertStringContainsString('# Security Configuration', $output);
        $this->assertStringContainsString('ServerSignature Off', $output);
        $this->assertStringContainsString('X-XSS-Protection', $output);
        $this->assertStringContainsString('X-Frame-Options', $output);
        $this->assertStringContainsString('X-Content-Type-Options', $output);
    }

    public function testMixedDirectiveTypes(): void
    {
        $htaccess = new HtAccess();
        $htaccess
            ->addDirective('Options -Indexes')  // String directive
            ->addDirective(new ServerSignature('Off'))  // Directive object
            ->addDirective(new Comment('Mixed types test'));  // Comment

        $container = new BasicAuthModule();
        $container->process([
            'realm' => 'Protected Area',
            'userFile' => '.htpasswd'
        ]);
        $htaccess->addDirective($container);  // Container

        $output = $htaccess->toString();

        $this->assertStringContainsString('Options -Indexes', $output);
        $this->assertStringContainsString('ServerSignature Off', $output);
        $this->assertStringContainsString('# Mixed types test', $output);
        $this->assertStringContainsString('AuthType Basic', $output);
    }

    public function testToStringWithEmptyDirectives(): void
    {
        $htaccess = new HtAccess();
        $output = $htaccess->toString();
        $this->assertEquals('', $output);
    }

    public function testMultipleDirectivesOrdering(): void
    {
        $htaccess = new HtAccess();
        $htaccess
            ->addDirective('ServerSignature Off')
            ->addDirective('DirectoryIndex index.php')
            ->addDirective(new Comment('Security settings'));

        $output = $htaccess->toString();

        $lines = explode("\n", $output);
        $nonEmptyLines = array_filter($lines, fn($line) => trim($line) !== '');

        $this->assertStringContainsString('ServerSignature Off', $nonEmptyLines[0] ?? '');
        $this->assertStringContainsString('DirectoryIndex index.php', $nonEmptyLines[1] ?? '');
        $this->assertStringContainsString('# Security settings', $nonEmptyLines[2] ?? '');
    }
}
