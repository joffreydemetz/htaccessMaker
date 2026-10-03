<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\IndexIgnore;

class IndexIgnoreTest extends DirectiveTest
{
    public function testIndexIgnoreWithSingleFile(): void
    {
        $directive = new IndexIgnore('*.tmp');
        $this->assertEquals('*.tmp', $directive->value());
    }

    public function testIndexIgnoreToString(): void
    {
        $directive = new IndexIgnore('*.tmp');
        $this->assertEquals('IndexIgnore *.tmp', $directive->toString());
    }

    public function testIndexIgnoreWithMultiplePatterns(): void
    {
        $directive = new IndexIgnore('*.tmp *.log *.bak');
        $this->assertEquals('*.tmp *.log *.bak', $directive->value());
    }

    public function testIndexIgnoreWithComplexPattern(): void
    {
        $directive = new IndexIgnore('*.tmp *.log .* backup* temp[0-9]*');
        $this->assertEquals('IndexIgnore *.tmp *.log .* backup* temp[0-9]*', $directive->toString());
    }
}
