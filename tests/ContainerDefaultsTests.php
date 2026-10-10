<?php

declare(strict_types=1);

namespace Tests;

/**
 * For containers that render something once enabled with their defaults.
 */
trait ContainerDefaultsTests
{
    public function testContainerDefaults(): void
    {
        $container = $this->newContainer();
        $container->process(['enabled' => true]);

        $this->assertNotEmpty($container->toString());
    }
}