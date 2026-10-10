<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\BaseContainerTest;
use Tests\DirectiveContainerTests;
use Tests\ContainerDefaultsTests;
use JDZ\HtaccessMaker\Module\RewriteModule;

class RewriteModuleTest extends BaseContainerTest
{
    use DirectiveContainerTests;
    use ContainerDefaultsTests;

    protected string $containerClass = RewriteModule::class;

    public function testRewriteModuleCreation(): void
    {
        $container = new RewriteModule();
        $this->assertInstanceOf(RewriteModule::class, $container);
    }

    public function testRewriteModuleWithBasicRewrite(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteRule ^old-page$ /new-page [R=301,L]');
        $container->ensureApacheCompatibility();

        $output = $container->toString(true);

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('RewriteRule ^old-page$ /new-page [R=301,L]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testRewriteModuleWithConditions(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]');
        $container->addDirective('RewriteRule ^(.*)$ http://%1/$1 [R=301,L]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]', $output);
        $this->assertStringContainsString('RewriteRule ^(.*)$ http://%1/$1 [R=301,L]', $output);
    }

    public function testRewriteModuleWithHttpsRedirect(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteCond %{HTTPS} off');
        $container->addDirective('RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteCond %{HTTPS} off', $output);
        $this->assertStringContainsString('RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]', $output);
    }

    public function testRewriteModuleWithPrettyUrls(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteBase /');
        $container->addDirective('RewriteCond %{REQUEST_FILENAME} !-f');
        $container->addDirective('RewriteCond %{REQUEST_FILENAME} !-d');
        $container->addDirective('RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteBase /', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_FILENAME} !-f', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_FILENAME} !-d', $output);
        $this->assertStringContainsString('RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]', $output);
    }

    public function testRewriteModuleWithFileExtensionRemoval(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteCond %{REQUEST_FILENAME} !-d');
        $container->addDirective('RewriteCond %{REQUEST_FILENAME} !-f');
        $container->addDirective('RewriteRule ^([^\.]+)$ $1.php [NC,L]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteRule ^([^\.]+)$ $1.php [NC,L]', $output);
    }

    public function testRewriteModuleWithTrailingSlash(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteCond %{REQUEST_FILENAME} !-f');
        $container->addDirective('RewriteRule ^([^/]+)/$ $1 [R=301,L]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteRule ^([^/]+)/$ $1 [R=301,L]', $output);
    }

    public function testRewriteModuleWithMultipleConditions(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteCond %{REQUEST_METHOD} ^POST$ [NC]');
        $container->addDirective('RewriteCond %{HTTP_REFERER} !^https://example\.com [NC]');
        $container->addDirective('RewriteRule ^(.*)$ - [F]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteCond %{REQUEST_METHOD} ^POST$ [NC]', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_REFERER} !^https://example\.com [NC]', $output);
        $this->assertStringContainsString('RewriteRule ^(.*)$ - [F]', $output);
    }

    public function testRewriteModuleWithComments(): void
    {
        $container = new RewriteModule();
        $container->addDirective('# Enable URL rewriting');
        $container->addDirective('RewriteEngine On');
        $container->addDirective('# Remove trailing slash');
        $container->addDirective('RewriteRule ^(.+)/$ /$1 [R=301,L]');

        $output = $container->toString(true);

        $this->assertStringContainsString('# Enable URL rewriting', $output);
        $this->assertStringContainsString('# Remove trailing slash', $output);
    }

    public function testRewriteModuleWithoutComments(): void
    {
        $container = new RewriteModule();
        $container->addDirective('# This is a comment');
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteRule ^test$ /test.php [L]');

        $output = $container->toString(false);

        $this->assertStringNotContainsString('# This is a comment', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('RewriteRule ^test$ /test.php [L]', $output);
    }

    public function testRewriteModuleEmpty(): void
    {
        $container = new RewriteModule();
        $container->ensureApacheCompatibility();

        $output = $container->toString(true);

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testRewriteModuleFluentInterface(): void
    {
        $container = new RewriteModule();
        $result = $container->addDirective('RewriteEngine On');

        $this->assertSame($container, $result);

        $output = $container->toString(true);
        $this->assertStringContainsString('RewriteEngine On', $output);
    }

    public function testRewriteModuleWithQueryStringAppend(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteRule ^api/(.*)$ /api.php?endpoint=$1 [L,QSA]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteRule ^api/(.*)$ /api.php?endpoint=$1 [L,QSA]', $output);
    }

    public function testRewriteModuleWithEnvironmentVariables(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteCond %{HTTP_USER_AGENT} bot [NC]');
        $container->addDirective('RewriteRule .* - [E=is_bot:1]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteCond %{HTTP_USER_AGENT} bot [NC]', $output);
        $this->assertStringContainsString('RewriteRule .* - [E=is_bot:1]', $output);
    }

    public function testRewriteModuleWithGone(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteRule ^old-section/.*$ - [G]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteRule ^old-section/.*$ - [G]', $output);
    }

    public function testRewriteModuleWithForbidden(): void
    {
        $container = new RewriteModule();
        $container->addDirective('RewriteEngine On');
        $container->addDirective('RewriteRule ^admin/.*$ - [F]');

        $output = $container->toString(true);

        $this->assertStringContainsString('RewriteRule ^admin/.*$ - [F]', $output);
    }
}
