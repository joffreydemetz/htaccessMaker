<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\Options;

class OptionsTest extends DirectiveTest
{
    public function testOptionsWithEnableIndexes(): void
    {
        $directive = new Options('+Indexes');
        $this->assertStringContainsString('Options +Indexes', $directive->toString());
    }

    public function testOptionsWithDisableIndexes(): void
    {
        $directive = new Options('-Indexes');
        $this->assertStringContainsString('Options -Indexes', $directive->toString());
    }

    public function testOptionsWithMultipleOptions(): void
    {
        $directive = new Options('+Indexes +FollowSymLinks -MultiViews');

        $this->assertStringContainsString('Options +Indexes +FollowSymLinks -MultiViews', $directive->toString());
    }
}
