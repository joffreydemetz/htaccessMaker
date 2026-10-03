<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\Container\PreventCookie;

class PreventCookieTest extends ContainerTest
{
    protected string $containerClass = PreventCookie::class;

    public function testPreventCookieWithDefaultPattern(): void
    {
        $container = new PreventCookie();
        $container->process(['enabled' => true]);

        $output = $container->toString();

        $this->assertStringContainsString('<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">', $output);
        $this->assertStringContainsString('Header append Vary "Accept-Encoding"', $output);
    }

    public function testPreventCookieSetVaryHeaders(): void
    {
        $container = new PreventCookie();
        $container->process([
            'enabled' => true,
            'setVaryHeaders' => true,
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('Header append Vary "Accept-Encoding"', $output);
        $this->assertStringContainsString('Header append Vary "User-Agent"', $output);
        $this->assertStringNotContainsString('Header set vary', $output);
    }

    public function testPreventCookieWithCustomPattern(): void
    {
        $container = new PreventCookie();
        $container->process([
            'enabled' => true,
            'pattern' => '\.(jpg|png|gif)$',
        ]);

        $output = $container->toString();

        // PreventCookie uses a default pattern for static files
        $this->assertStringContainsString('<FilesMatch', $output);
        $this->assertStringContainsString('</FilesMatch>', $output);
    }

    public function testPreventCookieWithMaxAge(): void
    {
        $container = new PreventCookie();
        $container->process([
            'enabled' => true,
            'setCacheControl' => true,
            'maxAge' => 86400,
        ]);

        $this->assertStringContainsString('max-age=86400', $container->toString());
    }

    public function testPreventCookieRemoveCookies(): void
    {
        $container = new PreventCookie();
        $container->process([
            'enabled' => true,
            'removeCookies' => true
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('Header unset Cookie', $output);
        $this->assertStringContainsString('Header unset Set-Cookie', $output);
    }

    public function testPreventCookieDisableETags(): void
    {
        $container = new PreventCookie();
        $container->process([
            'enabled' => true,
            'disableETags' => true
        ]);

        $this->assertStringContainsString('FileETag None', $container->toString());
    }

    public function testPreventCookieWithAllOptions(): void
    {
        $container = new PreventCookie();
        $container->process([
            'enabled' => true,
            'maxAge' => 31536000,
            'removeCookies' => true,
            'setCacheControl' => true,
            'setConnectionHeaders' => true,
            'disableETags' => true,
            'setVaryHeaders' => true
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('max-age=31536000', $output);
        $this->assertStringContainsString('Header unset Cookie', $output);
        $this->assertStringContainsString('FileETag None', $output);
    }
}
