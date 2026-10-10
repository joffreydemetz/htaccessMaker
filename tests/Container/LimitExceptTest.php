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

    public function testLimitExceptWithMethods(): void
    {
        $container = new LimitExcept();
        $container->process(['authMethods' => ['GET', 'POST']]);
        $container->addDirective('Order deny,allow');
        $container->addDirective('Deny from all');

        $output = $container->toString();

        $this->assertStringContainsString('<LimitExcept GET POST>', $output);
        $this->assertStringContainsString('Order deny,allow', $output);
        $this->assertStringContainsString('Deny from all', $output);
        $this->assertStringContainsString('</LimitExcept>', $output);
    }

    public function testLimitExceptWithIPRestrictions(): void
    {
        $container = new LimitExcept();
        $container->process(['authMethods' => ['GET', 'POST', 'HEAD', 'OPTIONS']]);
        $container->addDirective('Order deny,allow');
        $container->addDirective('Deny from all');
        $container->addDirective('Allow from 127.0.0.1');
        $container->addDirective('Allow from 192.168.1.0/24');

        $output = $container->toString();

        $this->assertStringContainsString('<LimitExcept GET POST HEAD OPTIONS>', $output);
        $this->assertStringContainsString('Order deny,allow', $output);
        $this->assertStringContainsString('Deny from all', $output);
        $this->assertStringContainsString('Allow from 127.0.0.1', $output);
        $this->assertStringContainsString('Allow from 192.168.1.0/24', $output);
    }
}
