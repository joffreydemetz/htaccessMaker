<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\CspContainer;

class CspContainerTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = CspContainer::class;

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $container = new CspContainer();
        $container->process($config);

        $this->assertSame($expected, $container->toString());
    }

    public static function processCases(): array
    {
        return [
            'default policy' => [
                ['enabled' => true],
                "Header set Content-Security-Policy \"default-src 'self'; child-src 'self'; connect-src 'self'; font-src 'self'; img-src 'self' data:; "
                    . "manifest-src 'self'; media-src 'self'; object-src 'self'; script-src 'self'; style-src 'self';\"\n\n",
            ],
            'csp entries replace their default, X- header' => [
                ['useXContentSecurityPolicy' => true, 'csp' => ['default' => ['none'], 'script' => ['self', 'https://cdn.example.com']]],
                "Header set X-Content-Security-Policy \"default-src 'none'; child-src 'self'; connect-src 'self'; font-src 'self'; img-src 'self' data:; "
                    . "manifest-src 'self'; media-src 'self'; object-src 'self'; script-src 'self' https://cdn.example.com; style-src 'self';\"\n\n",
            ],
            'full directive names, sources as one string' => [
                ['csp' => ['default-src' => "'none'", 'script-src' => "'self' 'unsafe-inline'", 'report-uri' => '/csp-report-endpoint']],
                "Header set Content-Security-Policy \"default-src 'none'; child-src 'self'; connect-src 'self'; font-src 'self'; img-src 'self' data:; "
                    . "manifest-src 'self'; media-src 'self'; object-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self'; report-uri /csp-report-endpoint;\"\n\n",
            ],
            'integrations add their hosts' => [
                ['integrations' => ['googleFonts']],
                "Header set Content-Security-Policy \"default-src 'self'; child-src 'self'; connect-src 'self'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; "
                    . "manifest-src 'self'; media-src 'self'; object-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com;\"\n\n",
            ],
        ];
    }
}
