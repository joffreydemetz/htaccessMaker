<?php

declare(strict_types=1);

namespace Tests;

use Tests\BaseContainerTest;
use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive\ServerSignature;

class ContainerTest extends BaseContainerTest
{
    protected bool $noDefaultsTest = true;
    public function testContainerAddDirectiveString(): void
    {
        $container = new Container();
        $container->addDirective('ServerSignature Off');

        $this->assertStringContainsString('ServerSignature Off', $container->toString());
    }

    public function testContainerAddDirectiveObject(): void
    {
        $container = new Container();
        $container->addDirective(new ServerSignature('Off'));

        $this->assertStringContainsString('ServerSignature Off', $container->toString());
    }

    public function testContainerAddNestedContainer(): void
    {
        $child = new Container();
        $child->addDirective('Directive "in child container"');

        $parent = new Container();
        $child->addDirective('Directive "in parent container"');
        $parent->addDirective($child);

        $this->assertStringContainsString('Directive "in child container"', $child->toString());
        $this->assertStringContainsString('Directive "in parent container"', $parent->toString());
    }

    // 
}
