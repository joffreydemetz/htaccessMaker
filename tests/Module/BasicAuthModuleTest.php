<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Module\BasicAuthModule;

class BasicAuthModuleTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = BasicAuthModule::class;

    private const FULL_CONFIG = [
        'authName' => 'Staging',
        'authUserFile' => '/var/www/.htpasswd',
        'allowedPaths' => ['^/api/', '^/health$'],
        'allowedUserAgents' => ['Googlebot', 'bingbot'],
        'passwordComment' => 'dev:secret',
        'defaultPaths' => false,
    ];

    #[DataProvider('processCases')]
    public function testProcess(array $config, bool $showComments, string $expected): void
    {
        $module = new BasicAuthModule();
        $module->process($config);

        $this->assertSame($expected, $module->toString($showComments));
    }

    public static function processCases(): array
    {
        return [
            'defaults: the favicon manifests stay public' => [['enabled' => true], true, implode("\n", [
                '# Basic Authentication',
                'SetEnvIf Request_URI "favicon/manifest\.json$" ForceAllow',
                'SetEnvIf Request_URI "favicon/browserconfig\.xml$" ForceAllow',
                'AuthName "Protected Area"',
                'AuthType Basic',
                'AuthUserFile /path/to/.htpasswd',
                'Require valid-user',
                'Order deny,allow',
                'Deny from all',
                'Allow from env=ForceAllow',
                'Satisfy Any',
                '',
                '',
            ])],
            'every key: allowed paths, then user agents, then the auth block' => [self::FULL_CONFIG, true, implode("\n", [
                '# Basic Authentication',
                'SetEnvIf Request_URI "^/api/" ForceAllow',
                'SetEnvIf Request_URI "^/health$" ForceAllow',
                'SetEnvIfNoCase User-Agent "Googlebot" ForceAllow',
                'SetEnvIfNoCase User-Agent "bingbot" ForceAllow',
                'AuthName "Staging"',
                'AuthType Basic',
                'AuthUserFile /var/www/.htpasswd',
                'Require valid-user',
                'Order deny,allow',
                'Deny from all',
                'Allow from env=ForceAllow',
                'Satisfy Any',
                '# dev:secret',
                '',
                '',
            ])],
            'comments hidden: the password comment is forced' => [self::FULL_CONFIG, false, implode("\n", [
                'SetEnvIf Request_URI "^/api/" ForceAllow',
                'SetEnvIf Request_URI "^/health$" ForceAllow',
                'SetEnvIfNoCase User-Agent "Googlebot" ForceAllow',
                'SetEnvIfNoCase User-Agent "bingbot" ForceAllow',
                'AuthName "Staging"',
                'AuthType Basic',
                'AuthUserFile /var/www/.htpasswd',
                'Require valid-user',
                'Order deny,allow',
                'Deny from all',
                'Allow from env=ForceAllow',
                'Satisfy Any',
                '# dev:secret',
                '',
                '',
            ])],
        ];
    }

    public function testWrappedWithApacheCompatibility(): void
    {
        $module = new BasicAuthModule();
        $module->process(['authUserFile' => '/var/www/.htpasswd', 'defaultPaths' => false]);
        $module->ensureApacheCompatibility();

        $this->assertSame(implode("\n", [
            '<IfModule mod_auth_basic.c>',
            '  # Basic Authentication',
            '  AuthName "Protected Area"',
            '  AuthType Basic',
            '  AuthUserFile /var/www/.htpasswd',
            '  Require valid-user',
            '  Order deny,allow',
            '  Deny from all',
            '  Allow from env=ForceAllow',
            '  Satisfy Any',
            '</IfModule>',
            '',
            '',
        ]), $module->toString());
    }
}
