<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Module\RoutingRewrite;

class RoutingRewriteTest extends BaseContainerTest
{
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = RoutingRewrite::class;

    public function testRoutingRewriteWithCustomBaseUrl(): void
    {
        $container = new RoutingRewrite();
        $container->process(['baseUrl' => '/app/']);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteBase /app/', $output);
    }

    public function testRoutingRewriteWithoutTrailingSlash(): void
    {
        $container = new RoutingRewrite();
        $container->process(['checkTrailingSlash' => false]);

        $output = $container->toString();

        // No trailing slash rewrite rule
        $this->assertStringNotContainsString('RewriteRule (.*)$ %{REQUEST_URI}/', $output);
    }

    public function testRoutingRewriteWithCustomController(): void
    {
        $container = new RoutingRewrite();
        $container->process(['defaultController' => 'app.php']);

        $output = $container->toString();

        $this->assertStringContainsString('app.php', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/app.php', $output);
    }

    public function testRoutingRewriteWithCustomRules(): void
    {
        $rules = [
            ['from' => '^old-page$', 'to' => '/new-page', 'flags' => ['R=301', 'L']],
            ['from' => '^legacy/(.*)$', 'to' => '/modern/$1', 'flags' => ['R=302', 'L']]
        ];

        $container = new RoutingRewrite();
        $container->process(['rules' => $rules]);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteRule ^old-page$ /new-page [R=301,L]', $output);
        $this->assertStringContainsString('RewriteRule ^legacy/(.*)$ /modern/$1 [R=302,L]', $output);
    }

    public function testRoutingRewriteWithoutVersionedFiles(): void
    {
        $container = new RoutingRewrite();
        $container->process(['versionedFiles' => false]);

        $output = $container->toString();

        $this->assertStringNotContainsString('Rewrite versioned files', $output);
    }

    public function testRoutingRewriteWithDomainApps(): void
    {
        $domainApps = [
            ['domain' => 'api.example.com', 'file' => 'api.php'],
            ['domain' => 'admin.example.com', 'file' => 'admin.php']
        ];

        $container = new RoutingRewrite();
        $container->process(['domainApps' => $domainApps]);

        $output = $container->toString();

        $this->assertStringContainsString('App api.php controller', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_HOST} =api.example.com', $output);
        $this->assertStringContainsString('App admin.php controller', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_HOST} =admin.example.com', $output);
    }

    public function testRoutingRewriteTrailingSlashLogic(): void
    {
        $container = new RoutingRewrite();
        $container->process([
            'checkTrailingSlash' => true
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !(/$|\.)', $output);
        $this->assertStringContainsString('RewriteRule (.*)$ %{REQUEST_URI}/ [R=301,L]', $output);
    }

    public function testRoutingRewriteDefaultControllerConditions(): void
    {
        $container = new RoutingRewrite();
        $container->process([
            'defaultController' => 'index.php'
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/index.php', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_FILENAME} !-f', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_FILENAME} !-d', $output);
        $this->assertStringContainsString('RewriteRule ^ index.php [L]', $output);
    }

    public function testRoutingRewriteVersionedFiles(): void
    {
        $container = new RoutingRewrite();
        $container->process(['versionedFiles' => true]);

        $output = $container->toString();

        $this->assertStringContainsString('Rewrite versioned files', $output);
        $this->assertStringContainsString('RewriteRule ^(.+)_(\d+)\.(js|css|png|jpg|jpeg|gif|pdf)$ $1.$3 [L]', $output);
    }
}
