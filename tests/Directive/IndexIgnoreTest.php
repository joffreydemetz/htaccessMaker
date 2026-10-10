<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\IndexIgnore;

class IndexIgnoreTest extends TestCase
{
    public function testIndexIgnoreToString(): void
    {
        $directive = new IndexIgnore('*.tmp *.log');

        $this->assertSame('IndexIgnore *.tmp *.log', $directive->toString());
    }
}
