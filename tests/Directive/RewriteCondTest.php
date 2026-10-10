<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\RewriteCond;

class RewriteCondTest extends TestCase
{
    public function testRewriteCondWithoutFlags(): void
    {
        $directive = new RewriteCond('%{REQUEST_METHOD}', '^POST$');

        $this->assertSame('RewriteCond %{REQUEST_METHOD} ^POST$', $directive->toString());
    }

    public function testRewriteCondWithFlags(): void
    {
        $directive = new RewriteCond('%{HTTP_HOST}', '^www\.example\.com$', ['NC', 'OR']);

        $this->assertSame('RewriteCond %{HTTP_HOST} ^www\.example\.com$ [NC,OR]', $directive->toString());
    }
}
