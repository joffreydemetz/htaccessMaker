<?php

declare(strict_types=1);

namespace Tests\Container;

use Tests\ContainerTest;
use JDZ\HtaccessMaker\Container\ErrorDocuments;

class ErrorDocumentsTest extends ContainerTest
{
    protected string $containerClass = ErrorDocuments::class;

    public function testErrorDocumentsWithBasicConfig(): void
    {
        $container = new ErrorDocuments();
        $container->process([
            'errorDocuments' => [
                '404' => '/404.html',
                '500' => '/500.html'
            ]
        ]);

        $output = $container->toString(true);

        $this->assertStringContainsString('ErrorDocument 404 /404.html', $output);
        $this->assertStringContainsString('ErrorDocument 500 /500.html', $output);
    }

    public function testErrorDocumentsWithCommonErrors(): void
    {
        $container = new ErrorDocuments();
        $container->addCommonErrorPages('');

        $output = $container->toString();

        $this->assertStringContainsString('ErrorDocument 400 /bad-request.html', $output);
        $this->assertStringContainsString('ErrorDocument 401 /unauthorized.html', $output);
        $this->assertStringContainsString('ErrorDocument 403 /forbidden.html', $output);
        $this->assertStringContainsString('ErrorDocument 404 /not-found.html', $output);
        $this->assertStringContainsString('ErrorDocument 500 /internal-error.html', $output);
        $this->assertStringContainsString('ErrorDocument 502 /bad-gateway.html', $output);
        $this->assertStringContainsString('ErrorDocument 503 /service-unavailable.html', $output);
    }

    public function testErrorDocumentsWithMessages(): void
    {
        $container = new ErrorDocuments();
        $container->process([
            'errorDocuments' => [
                '404' => '"Page Not Found"',
                '500' => '"Internal Server Error"'
            ]
        ]);

        $output = $container->toString();

        $this->assertStringContainsString('ErrorDocument 404 "Page Not Found"', $output);
        $this->assertStringContainsString('ErrorDocument 500 "Internal Server Error"', $output);
    }

    public function testErrorDocumentsWithManualDirectives(): void
    {
        $container = new ErrorDocuments();
        $container->addDirective('ErrorDocument 403 /forbidden.html');
        $container->addDirective('ErrorDocument 404 /not-found.html');

        $output = $container->toString(true);

        $this->assertStringContainsString('ErrorDocument 403 /forbidden.html', $output);
        $this->assertStringContainsString('ErrorDocument 404 /not-found.html', $output);
    }

    public function testErrorDocumentsEmpty(): void
    {
        $container = new ErrorDocuments();
        $this->assertIsString($container->toString());
    }
}
