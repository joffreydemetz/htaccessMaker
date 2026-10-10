<?php

declare(strict_types=1);

namespace Tests;

/**
 * For containers that render nothing until configured: process() without a config
 * adds nothing, even once forced into its tag. Modules don't qualify: their
 * constructor adds the base directive (RewriteEngine On, ExpiresActive On, ...).
 */
trait EmptyContainerTests
{
    public function testContainerEmpty(): void
    {
        $container = $this->newContainer();
        $container->process();
        $container->ensureApacheCompatibility();

        $this->assertSame('', $container->toString());
    }
}
