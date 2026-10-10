<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\ExpiresByType;

class ExpiresByTypeTest extends TestCase
{
    public function testExpiresByTypeQuotesTheExpiry(): void
    {
        $directive = new ExpiresByType('text/css', 'access plus 1 month');

        $this->assertSame('ExpiresByType text/css "access plus 1 month"', $directive->toString());
    }
}
