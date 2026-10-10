<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive;
use JDZ\HtaccessMaker\IfModule;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\Directive\ServerSignature;

class ContainerTest extends BaseContainerTest
{
    use EmptyContainerTests;

    /**
     * @param list<Directive|string> $directives
     */
    #[DataProvider('renderCases')]
    public function testToString(array $directives, bool $showComments, int $indent, string $expected): void
    {
        $container = new Container();
        foreach ($directives as $directive) {
            $container->addDirective($directive);
        }

        $this->assertSame($expected, $container->toString($showComments, $indent));
    }

    public static function renderCases(): array
    {
        return [
            'raw string, followed by a blank line' => [['ServerSignature Off'], true, 0, "ServerSignature Off\n\n"],
            'directive object' => [[new ServerSignature('Off')], true, 0, "ServerSignature Off\n\n"],
            'insertion order' => [['Directive "first"', 'Directive "second"'], true, 0, "Directive \"first\"\nDirective \"second\"\n\n"],
            'string comment shown' => [['# Test comment', 'Directive "value"'], true, 0, "# Test comment\nDirective \"value\"\n\n"],
            'string comment hidden' => [['# Test comment', 'Directive "value"'], false, 0, "Directive \"value\"\n\n"],
            'indented string comment hidden' => [['   # Test comment', 'Directive "value"'], false, 0, "Directive \"value\"\n\n"],
            'Comment hidden' => [[new Comment('Test comment'), 'Directive "value"'], false, 0, "Directive \"value\"\n\n"],
            'indent on every line' => [['# Test comment', 'Directive "value"'], true, 1, "  # Test comment\n  Directive \"value\"\n\n"],
            'blank strings skipped' => [['', '   ', 'Directive "value"'], true, 0, "Directive \"value\"\n\n"],
            'nothing left to render' => [['# Test comment'], false, 0, ''],
            'no directive' => [[], true, 0, ''],
        ];
    }

    public function testNestedContainerRendersInPlace(): void
    {
        $child = new Container();
        $child->addDirective('Directive "in child container"');

        $parent = new Container();
        $parent->addDirective('Directive "before child"');
        $parent->addDirective($child);
        $parent->addDirective('Directive "after child"');

        // the child keeps its trailing blank line; HtAccess collapses the run
        $this->assertSame(
            "Directive \"before child\"\nDirective \"in child container\"\n\n\nDirective \"after child\"\n\n",
            $parent->toString()
        );
    }

    public function testNestedEmptyContainerLeavesNoTrace(): void
    {
        $parent = new Container();
        $parent->addDirective('Directive "value"');
        $parent->addDirective(new Container());

        $this->assertSame("Directive \"value\"\n\n", $parent->toString());
    }

    public function testEnsureApacheCompatibilityReachesNestedModules(): void
    {
        $module = new IfModule('mod_headers.c');
        $module->addDirective('Header set X-Test 1');

        $inner = new Container();
        $inner->addDirective($module);

        $outer = new Container();
        $outer->addDirective($inner);
        $outer->ensureApacheCompatibility();

        $this->assertSame("<IfModule mod_headers.c>\n  Header set X-Test 1\n</IfModule>\n\n\n\n\n\n", $outer->toString());
    }
}
