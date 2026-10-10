<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Container;

/**
 * Base of the container tests that use EmptyContainerTests (an unconfigured
 * container renders nothing): which class to instantiate.
 */
abstract class BaseContainerTest extends TestCase
{
    protected string $containerClass = Container::class;

    protected function newContainer(): Container
    {
        return new $this->containerClass();
    }
}
