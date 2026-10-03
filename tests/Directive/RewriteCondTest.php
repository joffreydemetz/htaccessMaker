<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\RewriteCond;

class RewriteCondTest extends DirectiveTest
{
    public function testRewriteCondWithRequestMethod(): void
    {
        $directive = new RewriteCond('%{REQUEST_METHOD}', '^POST$');
        $this->assertStringContainsString('RewriteCond %{REQUEST_METHOD} ^POST$', $directive->toString());
    }

    public function testRewriteCondWithHttpHost(): void
    {
        $directive = new RewriteCond('%{HTTP_HOST}', '^www\.example\.com$', ['NC']);
        $this->assertStringContainsString('RewriteCond %{HTTP_HOST} ^www\.example\.com$ [NC]', $directive->toString());
    }
}
