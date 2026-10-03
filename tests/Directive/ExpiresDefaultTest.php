<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\ExpiresDefault;

class ExpiresDefaultTest extends DirectiveTest
{
    public function testExpiresDefaultValue(): void
    {
        $directive = new ExpiresDefault('A86400');
        $this->assertEquals('"A86400"', $directive->value());
    }

    public function testExpiresDefaultToString(): void
    {
        $directive = new ExpiresDefault('A2592000');
        $this->assertEquals('ExpiresDefault "A2592000"', $directive->toString());
    }

    public function testExpiresDefaultWithAccessPlusFormat(): void
    {
        $directive = new ExpiresDefault('access plus 1 month');
        $this->assertEquals('"access plus 1 month"', $directive->value());
    }

    public function testExpiresDefaultWithNowFormat(): void
    {
        $directive = new ExpiresDefault('now');
        $this->assertEquals('"now"', $directive->value());
    }
}
