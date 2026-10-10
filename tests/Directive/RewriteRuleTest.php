<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\RewriteRule;

class RewriteRuleTest extends TestCase
{
    public function testRewriteRuleWithFlags(): void
    {
        $directive = new RewriteRule('^old$', '/new', ['R=301', 'L']);

        $this->assertSame('RewriteRule ^old$ /new [R=301,L]', $directive->toString());
    }

    public function testRewriteRuleWithoutFlags(): void
    {
        $directive = new RewriteRule('^test$', '/test.php');

        $this->assertSame('RewriteRule ^test$ /test.php', $directive->toString());
    }
}
