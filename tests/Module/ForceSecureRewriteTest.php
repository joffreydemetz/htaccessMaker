<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\ForceSecureRewrite;

class ForceSecureRewriteTest extends TestCase
{
    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $module = new ForceSecureRewrite();
        $module->process($config);

        $this->assertSame($expected, $module->toString());
    }

    public static function processCases(): array
    {
        return [
            'every http request redirected' => [['enabled' => true], implode("\n", [
                'RewriteEngine On',
                'RewriteCond %{HTTPS} off',
                'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]',
                '',
                '',
            ])],
            'excluded paths' => [['excludePaths' => ['/api', '/webhook']], implode("\n", [
                'RewriteEngine On',
                'RewriteCond %{HTTPS} off',
                'RewriteCond %{REQUEST_URI} !^/api$',
                'RewriteCond %{REQUEST_URI} !^/webhook$',
                'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]',
                '',
                '',
            ])],
            'empty config adds nothing' => [[], "RewriteEngine On\n\n"],
        ];
    }
}
