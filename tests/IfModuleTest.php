<?php

declare(strict_types=1);

namespace Tests;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Module\DeflateModule;

class IfModuleTest extends TestCase
{
    /**
     * @param Closure(): IfModule $build
     */
    #[DataProvider('tagCases')]
    public function testTag(Closure $build, string $expected): void
    {
        $this->assertSame($expected, $build()->toString());
    }

    public static function tagCases(): array
    {
        $wrapped = "<IfModule mod_headers.c>\n  Header set X-Test 1\n</IfModule>\n\n";
        $bare = "Header set X-Test 1\n\n";

        return [
            'left out by default' => [static fn() => self::module(), $bare],
            'withIgnoreTag(false) prints it' => [static fn() => self::module()->withIgnoreTag(false), $wrapped],
            'withIgnoreTag() leaves it out again' => [static fn() => self::module()->withIgnoreTag(false)->withIgnoreTag(), $bare],
            'ensureApacheCompatibility() prints it' => [static fn() => self::module()->ensureApacheCompatibility(), $wrapped],
            'ensureApacheCompatibility() overrides withIgnoreTag()' => [static fn() => self::module()->withIgnoreTag()->ensureApacheCompatibility(), $wrapped],
            'withIgnoreTag() after ensureApacheCompatibility() wins' => [static fn() => self::module()->ensureApacheCompatibility()->withIgnoreTag(), $bare],
            'DeflateModule prints it by default' => [static fn() => new DeflateModule(), "<IfModule mod_deflate.c>\n  SetOutputFilter DEFLATE\n</IfModule>\n\n"],
            'withIgnoreTag() unwraps DeflateModule' => [static fn() => (new DeflateModule())->withIgnoreTag(), "SetOutputFilter DEFLATE\n\n"],
        ];
    }

    public function testIndentation(): void
    {
        $module = new IfModule('mod_test.c');
        $module->addDirective('TestDirective On');
        $module->ensureApacheCompatibility();

        $this->assertSame("  <IfModule mod_test.c>\n    TestDirective On\n  </IfModule>\n\n", $module->toString(true, 1));
    }

    public function testCommentsHiddenInsideTheTag(): void
    {
        $module = new IfModule('mod_test.c');
        $module->addDirective('# This comment should not appear');
        $module->addDirective('Options -Indexes');
        $module->ensureApacheCompatibility();

        $this->assertSame("<IfModule mod_test.c>\n  Options -Indexes\n</IfModule>\n\n", $module->toString(false));
    }

    public function testEmptyModuleRendersNoTag(): void
    {
        $module = new IfModule('mod_empty.c');
        $module->ensureApacheCompatibility();

        $this->assertSame('', $module->toString());
    }

    public function testNestedModuleIsIndentedUnderItsParent(): void
    {
        $inner = new IfModule('mod_ssl.c');
        $inner->addDirective('SSLEngine On');

        $outer = new IfModule('mod_rewrite.c');
        $outer->addDirective('RewriteEngine On');
        $outer->addDirective($inner);
        $outer->ensureApacheCompatibility();

        $this->assertSame(
            "<IfModule mod_rewrite.c>\n  RewriteEngine On\n  <IfModule mod_ssl.c>\n    SSLEngine On\n  </IfModule>\n\n\n</IfModule>\n\n",
            $outer->toString()
        );
    }

    private static function module(): IfModule
    {
        $module = new IfModule('mod_headers.c');
        $module->addDirective('Header set X-Test 1');

        return $module;
    }
}
