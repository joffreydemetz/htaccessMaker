<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\AntiXSS;

class AntiXSSTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = AntiXSS::class;

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $container = new AntiXSS();
        $container->process($config);

        $this->assertSame($expected, $container->toString());
    }

    public static function processCases(): array
    {
        return [
            'defaults, no HSTS' => [['enabled' => true], implode("\n", [
                'Header set X-XSS-Protection "1; mode=block"',
                'Header always append X-Frame-Options SAMEORIGIN',
                'Header set X-Content-Type-Options nosniff',
                'Header set Referrer-Policy "strict-origin-when-cross-origin"',
                '',
                '',
            ])],
            'custom values, HSTS quoted and always sent' => [[
                'frameOptions' => 'DENY',
                'refererPolicy' => 'no-referrer',
                'strictTransportSecurity' => 'max-age=31536000; includeSubDomains',
            ], implode("\n", [
                'Header set X-XSS-Protection "1; mode=block"',
                'Header always append X-Frame-Options DENY',
                'Header set X-Content-Type-Options nosniff',
                'Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"',
                'Header set Referrer-Policy "no-referrer"',
                '',
                '',
            ])],
            'every header switched off' => [[
                'xssProtection' => false,
                'frameOptions' => '',
                'contentTypeOptions' => null,
                'refererPolicy' => false,
                'strictTransportSecurity' => '',
            ], ''],
        ];
    }
}
