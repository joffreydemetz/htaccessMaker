<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive;
use JDZ\HtaccessMaker\EmptyLine;
use JDZ\HtaccessMaker\HtAccess;
use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Container\AntiXSS;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\Directive\ServerSignature;
use JDZ\HtaccessMaker\Module\BasicAuthModule;
use JDZ\HtaccessMaker\Module\DeflateModule;

class HtAccessTest extends TestCase
{
    public function testToStringWithEmptyDirectives(): void
    {
        $this->assertSame('', (new HtAccess())->toString());
    }

    public function testWithCommentsEnabledByDefault(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective(new Comment('Default comment behavior'));
        $htaccess->addDirective('# String comment');

        $this->assertSame("# Default comment behavior\n# String comment\n", $htaccess->toString());
    }

    public function testWithoutComments(): void
    {
        $htaccess = new HtAccess();
        $htaccess->withComments(false);
        $htaccess->addDirective(new Comment('This should not appear'));
        $htaccess->addDirective('# This should also not appear');
        $htaccess->addDirective(new ServerSignature('Off'));

        $this->assertSame("ServerSignature Off\n", $htaccess->toString());
    }

    public function testSettingsApplyWhenRendering(): void
    {
        $htaccess = (new HtAccess())
            ->withComments(false)
            ->addDirective('Options -Indexes')
            ->withComments(true)
            ->addDirective(new Comment('Now with comments'));

        $this->assertSame("Options -Indexes\n# Now with comments\n", $htaccess->toString());
    }

    // HtAccess and IfModule leave the <IfModule> tag out by default; DeflateModule always prints it
    public function testWithApacheCompatibilityDisabledByDefault(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective(self::headersModule());
        $htaccess->addDirective(new DeflateModule());

        $this->assertSame(
            "Header set X-Test 1\n\n<IfModule mod_deflate.c>\n  SetOutputFilter DEFLATE\n</IfModule>\n\n",
            $htaccess->toString()
        );
    }

    public function testWithApacheCompatibilityEnabled(): void
    {
        $htaccess = new HtAccess();
        $htaccess->withApacheCompatibility(true);
        $htaccess->addDirective(self::headersModule());
        $htaccess->addDirective(new DeflateModule());

        $this->assertSame(
            "<IfModule mod_headers.c>\n  Header set X-Test 1\n</IfModule>\n\n<IfModule mod_deflate.c>\n  SetOutputFilter DEFLATE\n</IfModule>\n\n",
            $htaccess->toString()
        );
    }

    public function testNoMoreThanTwoConsecutiveNewlines(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective("Line1\n\n\n\n\nLine2");
        $htaccess->addDirective(new Comment('Comment with spacing'));

        $this->assertSame("Line1\n\nLine2\n# Comment with spacing\n", $htaccess->toString());
    }

    public function testNoEmptyDirectivesRendered(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective('');
        $htaccess->addDirective('   ');
        $htaccess->addDirective("\n");
        $htaccess->addDirective(new Comment(''));
        $htaccess->addDirective('ServerSignature Off');

        $this->assertSame("ServerSignature Off\n", $htaccess->toString());
    }

    public function testEmptyLineRendersABlankLine(): void
    {
        $htaccess = new HtAccess();
        $htaccess->addDirective('ServerSignature Off');
        $htaccess->addDirective(new EmptyLine());
        $htaccess->addDirective('Options -Indexes');
        $htaccess->addDirective(new EmptyLine());
        $htaccess->addDirective(new EmptyLine());
        $htaccess->addDirective(new EmptyLine());
        $htaccess->addDirective('DirectoryIndex index.php');

        $this->assertSame("ServerSignature Off\n\nOptions -Indexes\n\nDirectoryIndex index.php\n", $htaccess->toString());
    }

    public function testComplexConfiguration(): void
    {
        $antiXss = new AntiXSS();
        $antiXss->process([
            'xssProtection' => '1; mode=block',
            'frameOptions' => 'SAMEORIGIN',
            'contentTypeOptions' => 'nosniff',
        ]);

        $htaccess = (new HtAccess())
            ->withApacheCompatibility(true)
            ->addDirective(new Comment('Security Configuration'))
            ->addDirective(new ServerSignature('Off'))
            ->addDirective($antiXss);

        $this->assertSame(
            "# Security Configuration\n"
                . "ServerSignature Off\n"
                . "Header set X-XSS-Protection \"1; mode=block\"\n"
                . "Header always append X-Frame-Options SAMEORIGIN\n"
                . "Header set X-Content-Type-Options nosniff\n"
                . "Header set Referrer-Policy \"strict-origin-when-cross-origin\"\n\n",
            $htaccess->toString()
        );
    }

    public function testMixedDirectiveTypes(): void
    {
        $auth = new BasicAuthModule();
        $auth->process([
            'authName' => 'Members',
            'authUserFile' => '/var/www/.htpasswd',
            'defaultPaths' => false,
        ]);

        $htaccess = (new HtAccess())
            ->addDirective('Options -Indexes')
            ->addDirective(new ServerSignature('Off'))
            ->addDirective(new Comment('Mixed types test'))
            ->addDirective($auth);

        $this->assertSame(
            "Options -Indexes\n"
                . "ServerSignature Off\n"
                . "# Mixed types test\n"
                . "# Basic Authentication\n"
                . "AuthName \"Members\"\n"
                . "AuthType Basic\n"
                . "AuthUserFile /var/www/.htpasswd\n"
                . "Require valid-user\n"
                . "Order deny,allow\n"
                . "Deny from all\n"
                . "Allow from env=ForceAllow\n"
                . "Satisfy Any\n\n",
            $htaccess->toString()
        );
    }

    #[DataProvider('stringifyCases')]
    public function testStringifyDirective(Directive|Container|string $directive, bool $showComments, bool $ensureApacheCompatibility, int $indent, string $expected): void
    {
        $this->assertSame($expected, HtAccess::stringifyDirective($directive, $showComments, $ensureApacheCompatibility, $indent));
    }

    public static function stringifyCases(): array
    {
        return [
            'directive' => [new ServerSignature('Off'), true, true, 0, 'ServerSignature Off'],
            'directive, indented' => [new ServerSignature('Off'), true, true, 2, '    ServerSignature Off'],
            'Comment shown' => [new Comment('Test comment'), true, true, 0, '# Test comment'],
            'Comment hidden' => [new Comment('Test comment'), false, true, 0, ''],
            'raw string' => ['ServerSignature Off', true, true, 0, 'ServerSignature Off'],
            'raw string comment shown' => ['# Test comment', true, true, 0, '# Test comment'],
            'raw string comment hidden' => ['  # Test comment', false, true, 0, ''],
            'module wrapped' => [self::headersModule(), true, true, 0, "<IfModule mod_headers.c>\n  Header set X-Test 1\n</IfModule>\n\n"],
            'module bare' => [self::headersModule(), true, false, 0, "Header set X-Test 1\n\n"],
        ];
    }

    public function testStringifyDirectiveWrapsModulesByDefault(): void
    {
        $this->assertSame(
            "<IfModule mod_headers.c>\n  Header set X-Test 1\n</IfModule>\n\n",
            HtAccess::stringifyDirective(self::headersModule())
        );
    }

    private static function headersModule(): IfModule
    {
        $module = new IfModule('mod_headers.c');
        $module->addDirective('Header set X-Test 1');

        return $module;
    }
}
