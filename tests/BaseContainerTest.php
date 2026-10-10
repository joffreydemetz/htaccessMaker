<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Container;

/**
 * Base of every container and module test: which class to instantiate.
 *
 * Opt-in traits, used only where they apply (no skip flags):
 * - EmptyContainerTests: an unconfigured container renders nothing
 * - DirectiveContainerTests: bare directives render as given
 * - ContainerDefaultsTests: process(['enabled' => true]) renders the defaults
 */
abstract class BaseContainerTest extends TestCase
{
    protected string $containerClass = Container::class;

    protected function newContainer(): Container
    {
        return new $this->containerClass();
    }
}
