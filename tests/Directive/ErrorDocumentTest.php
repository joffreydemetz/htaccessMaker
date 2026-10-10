<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\ErrorDocument;

class ErrorDocumentTest extends TestCase
{
    public function testErrorDocument(): void
    {
        $directive = new ErrorDocument(404, '/404.html');

        $this->assertSame('ErrorDocument 404 /404.html', $directive->toString());
    }
}
