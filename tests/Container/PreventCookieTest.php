<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\PreventCookie;

class PreventCookieTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = PreventCookie::class;

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $container = new PreventCookie();
        $container->process($config);

        $this->assertSame($expected, $container->toString());
    }

    public static function processCases(): array
    {
        return [
            'defaults, on the static files pattern' => [['enabled' => true], implode("\n", [
                '<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">',
                '  Header unset Cookie',
                '  Header unset Set-Cookie',
                '  Header set Cache-Control "public, max-age=31536000"',
                '  Header append Vary "Accept-Encoding"',
                '  Header append Vary "User-Agent"',
                '  Header append Connection "Keep-Alive"',
                '  Header append Keep-Alive "timeout=5, max=100"',
                '  FileETag None',
                '</FilesMatch>',
                '',
                '',
            ])],
            'cache control only, custom max-age' => [
                ['maxAge' => 86400, 'removeCookies' => false, 'setVaryHeaders' => false, 'setConnectionHeaders' => false, 'disableETags' => false],
                "<FilesMatch \"\\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$\">\n  Header set Cache-Control \"public, max-age=86400\"\n</FilesMatch>\n\n",
            ],
            'every option off' => [
                ['removeCookies' => false, 'setCacheControl' => false, 'setConnectionHeaders' => false, 'disableETags' => false, 'setVaryHeaders' => false],
                '',
            ],
        ];
    }
}
