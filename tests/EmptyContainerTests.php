<?php

declare(strict_types=1);

namespace Tests;

/**
 * For containers that render nothing until configured. Modules don't qualify:
 * their constructor adds the base directive (RewriteEngine On, ExpiresActive On, ...).
 */
trait EmptyContainerTests
{
    public function testContainerEmpty(): void
    {
        $container = $this->newContainer();
        $container->process();
        $container->ensureApacheCompatibility(false);
        $this->assertEquals('', $container->toString());
    }
}