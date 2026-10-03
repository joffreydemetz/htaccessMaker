<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\Module\SecurityRewrite;

class SecurityRewriteTest extends ContainerTest
{
    public function testSecurityRewriteWithUrlAttackBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addUrlAttackBlocking();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block out any script', $output);
        $this->assertStringContainsString('RewriteCond', $output);
        $this->assertStringContainsString('base64_encode', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithSqlInjectionBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addSqlInjectionBlocking();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block SQL injection attacks', $output);
        $this->assertStringContainsString('RewriteCond', $output);
        $this->assertStringContainsString('union|select|insert', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithShellInjectionBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addShellInjectionBlocking();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block shell injection', $output);
        $this->assertStringContainsString('RewriteCond', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithHttpMethodBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addHttpMethodBlocking(['TRACE', 'TRACK']);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block dangerous HTTP methods', $output);
        $this->assertStringContainsString('RewriteCond %{REQUEST_METHOD}', $output);
        $this->assertStringContainsString('TRACE|TRACK', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithUserAgentBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addUserAgentBlocking(['badbot', 'scraper']);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block malicious user agents', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_USER_AGENT}', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithReferrerBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addReferrerBlocking(['spam.com', 'malicious.net']);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block malicious referrers', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_REFERER}', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithRequestBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addRequestBlocking();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block malicious request patterns', $output);
        $this->assertStringContainsString('RewriteCond', $output);
        $this->assertStringContainsString('%00|%08|%09', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithAllSecurityRules(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addUrlAttackBlocking();
        $container->addSqlInjectionBlocking();
        $container->addShellInjectionBlocking();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);

        // Should contain multiple RewriteCond statements
        $condCount = substr_count($output, 'RewriteCond');
        $this->assertGreaterThan(5, $condCount);

        // Should contain multiple forbidden rules
        $forbiddenCount = substr_count($output, '[F]');
        $this->assertGreaterThan(2, $forbiddenCount);

        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithCustomBlockAction(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addUrlAttackBlocking('/blocked.html');

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('RewriteRule .* /blocked.html', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithCustomFlags(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addSqlInjectionBlocking('/error.html', ['R=403', 'L']);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('RewriteRule .* /error.html [R=403,L]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithEmptyUserAgentBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addUserAgentBlocking([], true);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block empty user agent strings', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_USER_AGENT} ^$', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithMultipleHttpMethods(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addHttpMethodBlocking(['HEAD', 'TRACE', 'TRACK', 'OPTIONS', 'PUT', 'DELETE']);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('HEAD|TRACE|TRACK|OPTIONS|PUT|DELETE', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithDefaultHttpMethods(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addHttpMethodBlocking();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('HEAD|TRACE|TRACK', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }
}
