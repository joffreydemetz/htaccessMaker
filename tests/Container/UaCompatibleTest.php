<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Container\UaCompatible;

class UaCompatibleTest extends BaseContainerTest
{
    use EmptyContainerTests;
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = UaCompatible::class;

    public function testUaCompatibleWithDefaultBrowsers(): void
    {
        $container = new UaCompatible();
        $container->process(['enabled'=>true]);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=Edge', $output);
    }

    public function testUaCompatibleWithCustomBrowsers(): void
    {
        $container = new UaCompatible();
        $container->process(['browsers' => 'IE=9']);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=9', $output);
    }

    public function testUaCompatibleWithEdgeMode(): void
    {
        $container = new UaCompatible();
        $container->process(['browsers' => 'IE=edge']);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=edge', $output);
    }

    public function testUaCompatibleWithChromeFrame(): void
    {
        $container = new UaCompatible();
        $container->process(['browsers' => 'IE=edge,chrome=1']);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=edge,chrome=1', $output);
    }

    public function testUaCompatibleWithStaticFilesUnset(): void
    {
        $container = new UaCompatible();
        $container->process(['unsetOnStaticFiles' => true]);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=Edge', $output);
        $this->assertStringContainsString('<FilesMatch', $output);
        $this->assertStringContainsString('Header unset X-UA-Compatible', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }

    public function testUaCompatibleWithoutStaticFilesUnset(): void
    {
        $container = new UaCompatible();
        $container->process(['unsetOnStaticFiles' => false]);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=Edge', $output);
        $this->assertStringNotContainsString('<FilesMatch', $output);
        $this->assertStringNotContainsString('Header unset', $output);
    }

    public function testUaCompatibleWithCustomStaticFileExtensions(): void
    {
        $container = new UaCompatible();
        $container->process([
            'staticFilesExtensions' => ['css', 'js', 'png'],
            'unsetOnStaticFiles' => true
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('<FilesMatch', $output);
        $this->assertStringContainsString('css|js|png', $output);
        $this->assertStringContainsString('Header unset X-UA-Compatible', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }

    public function testUaCompatibleWithComplexBrowserString(): void
    {
        $container = new UaCompatible();
        $container->process(['browsers' => 'IE=8,IE=9,chrome=1']);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=8,IE=9,chrome=1', $output);
    }

    public function testUaCompatibleWithMultipleConfigurations(): void
    {
        $container = new UaCompatible();
        $container->process([
            'browsers' => 'IE=edge,chrome=1',
            'staticFilesExtensions' => ['css', 'js', 'gif', 'png', 'jpg'],
            'unsetOnStaticFiles' => true
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('IE=edge,chrome=1', $output);
        $this->assertStringContainsString('<FilesMatch', $output);
        $this->assertStringContainsString('css|js|gif|png|jpg', $output);
        $this->assertStringContainsString('Header unset X-UA-Compatible', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }

    public function testUaCompatibleEmpty(): void
    {
        $container = new UaCompatible();

        $output = $container->toString();

        // Should be empty before process is called
        $this->assertEmpty(trim($output));
    }

    public function testUaCompatibleWithComments(): void
    {
        $container = new UaCompatible();
        $container->process(['enabled'=>true]);

        $outputWithComments = $container->toString(true);
        $outputWithoutComments = $container->toString(false);

        // Both should contain the directive
        $this->assertStringContainsString('Header set X-UA-Compatible', $outputWithComments);
        $this->assertStringContainsString('Header set X-UA-Compatible', $outputWithoutComments);
    }

    public function testUaCompatibleWithAllDefaultExtensions(): void
    {
        $container = new UaCompatible();
        $container->process(['unsetOnStaticFiles' => true]);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible', $output);
        $this->assertStringContainsString('<FilesMatch', $output);

        // Should contain default extensions
        $this->assertStringContainsString('js', $output);
        $this->assertStringContainsString('css', $output);
        $this->assertStringContainsString('png', $output);
        $this->assertStringContainsString('woff', $output);
        $this->assertStringContainsString('ico', $output);

        $this->assertStringContainsString('Header unset X-UA-Compatible', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }

    public function testUaCompatibleStructure(): void
    {
        $container = new UaCompatible();
        $container->process(['unsetOnStaticFiles' => true]);

        $output = $container->toString();

        // Should have main header first
        $headerPos = strpos($output, 'Header set X-UA-Compatible');
        $this->assertNotFalse($headerPos);

        // Then FilesMatch block
        $filesMatchPos = strpos($output, '<FilesMatch');
        $this->assertNotFalse($filesMatchPos);
        $this->assertGreaterThan($headerPos, $filesMatchPos);

        // Then unset header inside FilesMatch
        $unsetPos = strpos($output, 'Header unset X-UA-Compatible');
        $this->assertNotFalse($unsetPos);
        $this->assertGreaterThan($filesMatchPos, $unsetPos);
    }

    public function testUaCompatibleWithMinimalConfig(): void
    {
        $container = new UaCompatible();
        $container->process([
            'browsers' => 'IE=edge',
            'unsetOnStaticFiles' => false
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('Header set X-UA-Compatible "IE=edge"', $output);
        $this->assertStringNotContainsString('<FilesMatch', $output);
        $this->assertStringNotContainsString('Header unset', $output);
    }
}
