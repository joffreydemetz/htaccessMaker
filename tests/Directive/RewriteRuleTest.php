<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\RewriteRule;

class RewriteRuleTest extends DirectiveTest
{
    public function testRewriteRuleWithBasicRedirect(): void
    {
        $directive = new RewriteRule('^old$', '/new', ['R=301', 'L']);
        $this->assertStringContainsString('RewriteRule ^old$ /new [R=301,L]', $directive->toString());
    }

    public function testRewriteRuleWithoutFlags(): void
    {
        $directive = new RewriteRule('^test$', '/test.php');
        $this->assertStringContainsString('RewriteRule ^test$ /test.php', $directive->toString());
    }
}
