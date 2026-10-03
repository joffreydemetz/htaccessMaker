<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\Module\BasicAuthModule;

class BasicAuthModuleTest extends ContainerTest
{
    protected string $containerClass = BasicAuthModule::class;
    protected bool $noDefaultsTest = true;
    protected bool $skipDirectiveTests = true;

    public function testBasicAuthModuleWithSimpleAuth(): void
    {
        $container = new BasicAuthModule();
        $container->addDirective('AuthType Basic');
        $container->addDirective('AuthName "Protected Area"');
        $container->addDirective('AuthUserFile /path/to/.htpasswd');
        $container->addDirective('Require valid-user');
        $container->ensureApacheCompatibility();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_auth_basic.c>', $output);
        $this->assertStringContainsString('AuthType Basic', $output);
        $this->assertStringContainsString('AuthName "Protected Area"', $output);
        $this->assertStringContainsString('AuthUserFile /path/to/.htpasswd', $output);
        $this->assertStringContainsString('Require valid-user', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }
}
