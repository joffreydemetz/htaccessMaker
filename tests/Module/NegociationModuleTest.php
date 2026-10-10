<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Module\NegociationModule;

class NegociationModuleTest extends BaseContainerTest
{
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = NegociationModule::class;

    public function testNegociationModuleWithMultiViewsEnabled(): void
    {
        $container = new NegociationModule();
        $container->enableMultiViews();
        $container->ensureApacheCompatibility();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_negotiation.c>', $output);
        $this->assertStringContainsString('Options +MultiViews', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testNegociationModuleWithMultiViewsDisabled(): void
    {
        $container = new NegociationModule();
        $container->disableMultiViews();
        $container->ensureApacheCompatibility();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_negotiation.c>', $output);
        $this->assertStringContainsString('Options -MultiViews', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testNegociationModuleWithIndexIgnore(): void
    {
        $container = new NegociationModule();
        $container->addIndexIgnore('*.var');
        $container->ensureApacheCompatibility();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_negotiation.c>', $output);
        $this->assertStringContainsString('IndexIgnore *.var', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testNegociationModuleWithDefaultConfiguration(): void
    {
        $container = new NegociationModule();
        $container->process();
        $container->ensureApacheCompatibility();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_negotiation.c>', $output);
        $this->assertStringContainsString('Options -MultiViews', $output);
        $this->assertStringContainsString('IndexIgnore *', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testNegociationModuleWithMultipleOptions(): void
    {
        $container = new NegociationModule();
        $container->enableMultiViews();
        $container->addIndexIgnore('*.map');
        $container->ensureApacheCompatibility();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_negotiation.c>', $output);
        $this->assertStringContainsString('Options +MultiViews', $output);
        $this->assertStringContainsString('IndexIgnore *.map', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }
}
