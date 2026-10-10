<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Module\MaintenanceRewrite;

class MaintenanceRewriteTest extends BaseContainerTest
{
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = MaintenanceRewrite::class;

    public function testMaintenanceRewriteWithDefaults(): void
    {
        $container = new MaintenanceRewrite();
        $container->process(['enabled' => true]);
        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('/maintenance.html', $output);
    }

    public function testMaintenanceRewriteDefaultOff(): void
    {
        $container = new MaintenanceRewrite();
        $container->process(['enabled' => true, 'defaultState' => false]);
        $output = $container->toString(true);

        // When default state is OFF, maintenance rules should be commented
        $this->assertStringContainsString('# RewriteCond %{REQUEST_URI} !^/maintenance.html$', $output);
        $this->assertStringContainsString('# RewriteRule', $output);
    }

    public function testMaintenanceRewriteDefaultOn(): void
    {
        $container = new MaintenanceRewrite();
        $container->process(['enabled' => true, 'defaultState' => true]);
        $output = $container->toString(true);

        // When default state is ON, maintenance rules should be active
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/maintenance.html$', $output);
    }

    public function testMaintenanceRewriteWithAllowedIps(): void
    {
        $container = new MaintenanceRewrite();
        $container->process([
            'enabled' => true,
            'allowedIps' => ['192.168.1.1', '10.0.0.1'],
            'maintenanceFile' => '/maintenance.html',
            'defaultState' => true,
        ]);
        $output = $container->toString();

        $this->assertStringContainsString('RewriteCond %{REMOTE_ADDR} !^192\.168\.1\.1$', $output);
        $this->assertStringContainsString('RewriteCond %{REMOTE_ADDR} !^10\.0\.0\.1$', $output);
    }

    public function testMaintenanceRewriteWithCustomPage(): void
    {
        $container = new MaintenanceRewrite();
        $container->process([
            'enabled' => true,
            'maintenanceFile' => '/custom-maintenance.html',
            'defaultState' => true,
            'allowedIps' => [],
        ]);
        $output = $container->toString();

        $this->assertStringContainsString('/custom-maintenance.html', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/custom-maintenance.html$', $output);
    }

    public function testMaintenanceRewriteIpEscaping(): void
    {
        $container = new MaintenanceRewrite();
        $container->process([
            'enabled' => true,
            'allowedIps' => ['192.168.1.100'],
            'maintenanceFile' => '/maintenance.html',
            'defaultState' => true,
        ]);
        $output = $container->toString();

        // Verify that dots are properly escaped in regex
        $this->assertStringContainsString('192\.168\.1\.100', $output);
        $this->assertStringNotContainsString('192.168.1.100', $output);
    }

    public function testMaintenanceRewriteRedirectToHome(): void
    {
        $container = new MaintenanceRewrite();
        $container->process([
            'enabled' => true,
            'maintenanceFile' => '/maintenance.html',
            'defaultState' => false,
        ]);
        $output = $container->toString();

        // When maintenance is OFF, accessing maintenance page should redirect to home
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} ^/maintenance.html$', $output);
        $this->assertStringContainsString('RewriteRule ^ https://%{HTTP_HOST}/ [L,R=301]', $output);
    }

    public function testMaintenanceRewriteWithMultipleIps(): void
    {
        $allowedIps = ['127.0.0.1', '192.168.1.1', '10.0.0.1', '172.16.0.1'];

        $container = new MaintenanceRewrite();
        $container->process([
            'enabled' => true,
            'allowedIps' => $allowedIps,
            'maintenanceFile' => '/maintenance.html',
            'defaultState' => true,
        ]);
        $output = $container->toString();

        foreach ($allowedIps as $ip) {
            $escapedIp = str_replace('.', '\.', $ip);
            $this->assertStringContainsString("RewriteCond %{REMOTE_ADDR} !^{$escapedIp}$", $output);
        }
    }
}
