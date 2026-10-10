<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseContainerTest;
use Tests\EmptyContainerTests;
use JDZ\HtaccessMaker\Container\ErrorDocuments;

class ErrorDocumentsTest extends BaseContainerTest
{
    use EmptyContainerTests;

    protected string $containerClass = ErrorDocuments::class;

    #[DataProvider('processCases')]
    public function testProcess(array $config, string $expected): void
    {
        $container = new ErrorDocuments();
        $container->process($config);

        $this->assertSame($expected, $container->toString());
    }

    public static function processCases(): array
    {
        return [
            'default 404 page' => [['enabled' => true], "ErrorDocument 404 /error-404.html\n\n"],
            'code => url, a message kept as given' => [
                ['errorDocuments' => ['404' => '/404.html', '500' => '"Internal Server Error"']],
                "ErrorDocument 404 /404.html\nErrorDocument 500 \"Internal Server Error\"\n\n",
            ],
            'list of code + url' => [
                ['errorDocuments' => [['code' => 403, 'url' => '/errors/forbidden.html'], ['code' => 404, 'url' => '/errors/not-found.html']]],
                "ErrorDocument 403 /errors/forbidden.html\nErrorDocument 404 /errors/not-found.html\n\n",
            ],
            'common pages under a base URL, then the custom ones' => [
                ['errorDocuments' => [], 'commonErrorPages' => '/errors/', 'customErrors' => [404 => '/custom-404.html']],
                implode("\n", [
                    'ErrorDocument 400 /errors/bad-request.html',
                    'ErrorDocument 401 /errors/unauthorized.html',
                    'ErrorDocument 403 /errors/forbidden.html',
                    'ErrorDocument 404 /errors/not-found.html',
                    'ErrorDocument 500 /errors/internal-error.html',
                    'ErrorDocument 502 /errors/bad-gateway.html',
                    'ErrorDocument 503 /errors/service-unavailable.html',
                    'ErrorDocument 404 /custom-404.html',
                    '',
                    '',
                ]),
            ],
        ];
    }
}
