<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\RoutingRewrite;

class RoutingRewriteTest extends TestCase
{
    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $module = new RoutingRewrite();
        $module->process($config);

        $this->assertSame($expected, $module->toString());
    }

    public function testAppendRulesWithoutRulesAddsNothing(): void
    {
        $module = new RoutingRewrite();
        $module->appendRules([]);

        $this->assertSame("RewriteEngine On\n\n", $module->toString());
    }

    public static function processCases(): array
    {
        return [
            'defaults, even without a config' => [[], implode("\n", [
                'RewriteEngine On',
                'RewriteBase /',
                'RewriteCond %{REQUEST_URI} !(/$|\.)',
                'RewriteRule (.*)$ %{REQUEST_URI}/ [R=301,L]',
                '# Rewrite versioned files',
                'RewriteRule ^(.+)_(\d+)\.(js|css|png|jpg|jpeg|gif|pdf)$ $1.$3 [L]',
                'RewriteRule ^(.+)\.(js|css|png|jpg|jpeg|gif|pdf)\?(\d+)$ $1.$2 [L]',
                '# Default controller',
                'RewriteCond %{REQUEST_URI} !^/index.php',
                'RewriteCond %{REQUEST_FILENAME} !-f',
                'RewriteCond %{REQUEST_FILENAME} !-d',
                'RewriteRule ^ index.php [L]',
                '',
                '',
            ])],
            'base, rules (raw, default flags, own flags), domain app, controller' => [[
                'baseUrl' => '/app',
                'checkTrailingSlash' => false,
                'versionedFiles' => false,
                'rules' => [
                    'Redirect 410 /gone',
                    ['from' => '^old$', 'to' => '/new'],
                    ['from' => '^tmp$', 'to' => '/temp', 'flags' => ['R=302', 'L']],
                ],
                'domainApps' => [['domain' => 'api.example.com', 'file' => 'api.php']],
                'defaultController' => 'app.php',
            ], implode("\n", [
                'RewriteEngine On',
                'RewriteBase /app/',
                'Redirect 410 /gone',
                'RewriteRule ^old$ /new [R=301,L]',
                'RewriteRule ^tmp$ /temp [R=302,L]',
                '# App api.php controller',
                'RewriteCond %{HTTP_HOST} =api.example.com',
                'RewriteCond %{REQUEST_URI} !^/api.php',
                'RewriteCond %{REQUEST_FILENAME} !-f',
                'RewriteCond %{REQUEST_FILENAME} !-d',
                'RewriteRule .* api.php [L]',
                '# Default controller',
                'RewriteCond %{REQUEST_URI} !^/app.php',
                'RewriteCond %{REQUEST_FILENAME} !-f',
                'RewriteCond %{REQUEST_FILENAME} !-d',
                'RewriteRule ^ app.php [L]',
                '',
                '',
            ])],
        ];
    }
}
