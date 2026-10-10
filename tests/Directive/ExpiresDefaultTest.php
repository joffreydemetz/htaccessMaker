<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\ExpiresDefault;

class ExpiresDefaultTest extends TestCase
{
    public function testExpiresDefaultToString(): void
    {
        $directive = new ExpiresDefault('A2592000');

        $this->assertSame('ExpiresDefault "A2592000"', $directive->toString());
    }
}
