<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Container;

class BaseContainerTest extends TestCase
{
    protected string $containerClass = Container::class;
    protected bool $noCreationTest = false;
    protected bool $noEmptiesTest = false;
    protected bool $noDefaultsTest = false;
    protected bool $skipDirectiveTests = false;

    public function testContainerCreation(): void
    {
        if ($this->containerClass === '' || $this->noCreationTest) {
            $this->markTestSkipped('No creation test');
            return;
        }

        $container = new $this->containerClass();

        $this->assertInstanceOf($this->containerClass, $container);

        if (Container::class !== $this->containerClass) {
            $this->assertInstanceOf(Container::class, $container);
        }
    }

    public function testContainerEmpty(): void
    {
        if ($this->noEmptiesTest) {
            $this->markTestSkipped('No defaults test');
            return;
        }

        $container = new $this->containerClass();
        $container->process();
        $container->ensureApacheCompatibility(false);
        $this->assertEquals('', $container->toString());
    }

    public function testContainerDefaults(): void
    {
        if ($this->noDefaultsTest) {
            $this->markTestSkipped('No defaults test');
            return;
        }

        $container = new $this->containerClass();
        $container->process(['enabled' => true]);
        $result = $container->toString();
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testContainerIndentation(): void
    {
        if ($this->skipDirectiveTests) {
            $this->markTestSkipped('Container requires specific configuration');
        }

        $container = new $this->containerClass();
        $container->addDirective('# Test comment');
        $container->addDirective('TestDirective "value"');

        $output = $container->toString(true, 1);

        $this->assertStringContainsString('  # Test comment', $output);
        $this->assertStringContainsString('  TestDirective "value"', $output);
    }

    public function testContainerAddSingleDirective(): void
    {
        if ($this->skipDirectiveTests) {
            $this->markTestSkipped('Container requires specific configuration');
        }

        $container = new $this->containerClass();
        $container->addDirective('TestDirective "value"');
        $output = $container->toString();

        $this->assertStringContainsString('TestDirective "value"', $output);
    }

    public function testContainerAddMultipleDirectives(): void
    {
        if ($this->skipDirectiveTests) {
            $this->markTestSkipped('Container requires specific configuration');
        }

        $container = new $this->containerClass();
        $container->addDirective('Directive "first"');
        $container->addDirective('Directive "second"');

        $output = $container->toString();
        $this->assertStringContainsString('Directive "first"', $output);
        $this->assertStringContainsString('Directive "second"', $output);
    }

    public function testContainerWithComments(): void
    {
        if ($this->skipDirectiveTests) {
            $this->markTestSkipped('Container requires specific configuration');
        }

        $container = new $this->containerClass();
        $container->addDirective('# Test comment');
        $container->addDirective('Directive "toString with comments"');

        $output = $container->toString();
        $this->assertStringContainsString('# Test comment', $output);
        $this->assertStringContainsString('Directive "toString with comments"', $output);
    }

    public function testContainerWithNoComments(): void
    {
        if ($this->skipDirectiveTests) {
            $this->markTestSkipped('Container requires specific configuration');
        }

        $container = new $this->containerClass();
        $container->addDirective('# Test comment');
        $container->addDirective('Directives "toString with no comments"');

        $output = $container->toString(false);
        $this->assertStringNotContainsString('# Test comment', $output);
        $this->assertStringContainsString('Directives "toString with no comments"', $output);
    }

    public function testContainerWithMixedDirectiveTypes(): void
    {
        if ($this->skipDirectiveTests) {
            $this->markTestSkipped('Container requires specific configuration');
        }

        $container = new $this->containerClass();
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
        if ($this->skipDirectiveTests) {
            $this->markTestSkipped('Container requires specific configuration');
        }

        $container = new $this->containerClass();
        $result = $container->addDirective('Test1')->addDirective('Test2');

        $this->assertSame($container, $result);

        $output = $container->toString();
        $this->assertStringContainsString('Test1', $output);
        $this->assertStringContainsString('Test2', $output);
    }
}
