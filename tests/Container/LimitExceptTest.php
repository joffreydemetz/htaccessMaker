<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\LimitExcept;

class LimitExceptTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = LimitExcept::class;

    public function testMethodsListedOnce(): void
    {
        $container = new LimitExcept();
        $container->process(['authMethods' => ['GET', 'POST', 'GET']]);
        $container->addDirective('Require valid-user');

        $this->assertSame("<LimitExcept GET POST>\n  Require valid-user\n</LimitExcept>\n\n", $container->toString());
    }

    public function testMethodsKeptWhenProcessedAgainWithoutThem(): void
    {
        $container = new LimitExcept();
        $container->process(['authMethods' => ['GET']]);
        $container->process(['enabled' => true]);
        $container->addDirective('Require valid-user');

        $this->assertSame("<LimitExcept GET>\n  Require valid-user\n</LimitExcept>\n\n", $container->toString());
    }

    public function testRendersNothingWithoutMethods(): void
    {
        $container = new LimitExcept();
        $container->process(['enabled' => true]);
        $container->addDirective('Require valid-user');

        $this->assertSame('', $container->toString());
    }
}
