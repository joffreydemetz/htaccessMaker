<?php

declare(strict_types=1);

namespace Tests;

use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use Tests\DirectiveContainerTests;
use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive\ServerSignature;

class ContainerTest extends BaseContainerTest
{
    use EmptyContainerTests;
    use DirectiveContainerTests;

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
        $parent->addDirective('Directive "in parent container"');
        $parent->addDirective($child);

        $output = $parent->toString();
        $this->assertStringContainsString('Directive "in parent container"', $output);
        $this->assertStringContainsString('Directive "in child container"', $output);
        $this->assertLessThan(strpos($output, 'in child container'), strpos($output, 'in parent container'), 'The child renders after the parent\'s own directive');
    }

    // 
}
