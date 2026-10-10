<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\RewriteModule;

class RewriteModuleTest extends TestCase
{
    public function testStartsWithRewriteEngineOn(): void
    {
        $this->assertSame("RewriteEngine On\n\n", (new RewriteModule())->toString());
    }

    public function testWrappedWithApacheCompatibility(): void
    {
        $module = new RewriteModule();
        $module->ensureApacheCompatibility();

        $this->assertSame("<IfModule mod_rewrite.c>\n  RewriteEngine On\n</IfModule>\n\n", $module->toString());
    }

    public function testAddMethodsRenderInCallOrder(): void
    {
        $module = new RewriteModule();
        $module->addRewriteBase('/blog');
        $module->addRewriteCond('%{HTTP_HOST}', '^www\.(.+)$', ['NC']);
        $module->addRewriteRule('^(.*)$', 'https://%1/$1', ['R=301', 'L']);
        $module->addRewriteCond('%{REQUEST_FILENAME}', '!-f');
        $module->addRewriteRule('^(.*)$', 'index.php', ['QSA', 'L']);

        $this->assertSame(implode("\n", [
            'RewriteEngine On',
            'RewriteBase /blog/',
            'RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]',
            'RewriteRule ^(.*)$ https://%1/$1 [R=301,L]',
            'RewriteCond %{REQUEST_FILENAME} !-f',
            'RewriteRule ^(.*)$ index.php [QSA,L]',
            '',
            '',
        ]), $module->toString());
    }

    public function testAddRewriteCondAndRuleReturnTheAddedDirective(): void
    {
        $module = new RewriteModule();
        $module->addRewriteCond('%{HTTPS}', 'off')->setForceComment();
        $module->addRewriteRule('^', 'https://%{HTTP_HOST}%{REQUEST_URI}', ['R=301', 'L'])->setForceComment();

        $this->assertSame(implode("\n", [
            'RewriteEngine On',
            '# RewriteCond %{HTTPS} off',
            '# RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]',
            '',
            '',
        ]), $module->toString());
    }

    /**
     * Only the commented-out ON section and the OFF condition are pinned: the OFF rule
     * (`RewriteRule ^ https://%{HTTP_HOST} [L]`, no R flag) is reported, not pinned.
     */
    #[DataProvider('maintenanceCases')]
    public function testAddMaintenanceModeCommentsOutTheOnSection(bool $showComments, string $expectedStart): void
    {
        $module = new RewriteModule();
        $module->addMaintenanceMode(['10.0.0.1', '192.168.1.10'], '/down.html');

        $this->assertSame($expectedStart, $module->toString($showComments));
    }

    public static function maintenanceCases(): array
    {
        return [
            'comments shown' => [true, implode("\n", [
                'RewriteEngine On',
                '# Maintenance mode',
                '# RewriteCond %{REMOTE_ADDR} !^10\.0\.0\.1$',
                '# RewriteCond %{REMOTE_ADDR} !^192\.168\.1\.10$',
                '# RewriteCond %{REQUEST_URI} !^/down.html$',
                '# RewriteRule $ /down.html [L]',
                '# or not to maintenance',
                'RewriteCond %{REQUEST_URI} ^/down.html$',
                // a same-host absolute URL without R is an internal rewrite: the page was not left
                'RewriteRule ^ https://%{HTTP_HOST}/ [L,R=301]',
                '',
                '',
            ])],
            'comments hidden: the ON section stays, commented out' => [false, implode("\n", [
                'RewriteEngine On',
                '# RewriteCond %{REMOTE_ADDR} !^10\.0\.0\.1$',
                '# RewriteCond %{REMOTE_ADDR} !^192\.168\.1\.10$',
                '# RewriteCond %{REQUEST_URI} !^/down.html$',
                '# RewriteRule $ /down.html [L]',
                'RewriteCond %{REQUEST_URI} ^/down.html$',
                // a same-host absolute URL without R is an internal rewrite: the page was not left
                'RewriteRule ^ https://%{HTTP_HOST}/ [L,R=301]',
                '',
                '',
            ])],
        ];
    }
}
