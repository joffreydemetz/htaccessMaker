<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\RewriteBase;

class RewriteBaseTest extends DirectiveTest
{
    public function testRewriteBaseWithRoot(): void
    {
        $directive = new RewriteBase('/');
        $this->assertStringContainsString('RewriteBase /', $directive->toString());
    }

    public function testRewriteBaseWithSubdirectory(): void
    {
        $directive = new RewriteBase('/app/');
        $this->assertStringContainsString('RewriteBase /app/', $directive->toString());
    }

    public function testRewriteBaseWithoutTrailingSlash(): void
    {
        $directive = new RewriteBase('/api');
        $this->assertStringContainsString('RewriteBase /api/', $directive->toString());
    }

    public function testRewriteBaseWithEmptyPath(): void
    {
        $directive = new RewriteBase('');
        $this->assertStringContainsString('RewriteBase /', $directive->toString());
    }
}
