<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\RewriteEngine;

class RewriteEngineTest extends DirectiveTest
{
    public function testRewriteEngineOn(): void
    {
        $directive = new RewriteEngine('On');

        $output = $directive->toString(true);

        $this->assertStringContainsString('RewriteEngine On', $output);
    }
}
