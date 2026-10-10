<?php

declare(strict_types=1);

namespace Tests;

/**
 * For containers that render bare directives as given (used by BaseContainerTest subclasses).
 */
trait DirectiveContainerTests
{
    public function testContainerIndentation(): void
    {
        $container = $this->newContainer();
        $container->addDirective('# Test comment');
        $container->addDirective('TestDirective "value"');

        $output = $container->toString(true, 1);

        $this->assertStringContainsString('  # Test comment', $output);
        $this->assertStringContainsString('  TestDirective "value"', $output);
    }

    public function testContainerAddSingleDirective(): void
    {
        $container = $this->newContainer();
        $container->addDirective('TestDirective "value"');

        $this->assertStringContainsString('TestDirective "value"', $container->toString());
    }

    public function testContainerAddMultipleDirectives(): void
    {
        $container = $this->newContainer();
        $container->addDirective('Directive "first"');
        $container->addDirective('Directive "second"');

        $output = $container->toString();
        $this->assertStringContainsString('Directive "first"', $output);
        $this->assertStringContainsString('Directive "second"', $output);
    }

    public function testContainerWithComments(): void
    {
        $container = $this->newContainer();
        $container->addDirective('# Test comment');
        $container->addDirective('Directive "toString with comments"');

        $output = $container->toString();
        $this->assertStringContainsString('# Test comment', $output);
        $this->assertStringContainsString('Directive "toString with comments"', $output);
    }

    public function testContainerWithNoComments(): void
    {
        $container = $this->newContainer();
        $container->addDirective('# Test comment');
        $container->addDirective('Directives "toString with no comments"');

        $output = $container->toString(false);
        $this->assertStringNotContainsString('# Test comment', $output);
        $this->assertStringContainsString('Directives "toString with no comments"', $output);
    }

    public function testContainerWithMixedDirectiveTypes(): void
    {
        $container = $this->newContainer();
        $container->addDirective('Directive "first"');
        $container->addDirective('# Comment directive');
        $container->addDirective('Directive "second"');

        $output = $container->toString();
        $this->assertStringContainsString('Directive "first"', $output);
        $this->assertStringContainsString('# Comment directive', $output);
        $this->assertStringContainsString('Directive "second"', $output);
    }

    public function testContainerFluentInterface(): void
    {
        $container = $this->newContainer();
        $result = $container->addDirective('Test1')->addDirective('Test2');

        $this->assertSame($container, $result);

        $output = $container->toString();
        $this->assertStringContainsString('Test1', $output);
        $this->assertStringContainsString('Test2', $output);
    }
}