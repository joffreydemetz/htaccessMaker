<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\ErrorDocument;

class ErrorDocumentTest extends DirectiveTest
{
    public function testErrorDocument(): void
    {
        $directive = new ErrorDocument(404, '/404.html');

        $output = $directive->toString(true);

        $this->assertStringContainsString('ErrorDocument 404 /404.html', $output);
    }

    public function testErrorDocumentWithMessage(): void
    {
        $directive = new ErrorDocument(404, '"Page Not Found"');

        $output = $directive->toString(true);

        $this->assertStringContainsString('ErrorDocument 404 "Page Not Found"', $output);
    }

    public function testErrorDocumentWithEmptyPath(): void
    {
        $directive = new ErrorDocument(404, '');

        $output = $directive->toString(true);

        $this->assertStringContainsString('ErrorDocument 404 ', $output);
    }
}
