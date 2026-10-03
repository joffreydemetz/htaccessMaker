<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\ExpiresByType;

class ExpiresByTypeTest extends DirectiveTest
{
    public function testExpiresByTypeValue(): void
    {
        $directive = new ExpiresByType('text/html', 'A86400');
        $this->assertEquals('text/html "A86400"', $directive->value());
    }

    public function testExpiresByTypeWithAccessPlusFormat(): void
    {
        $directive = new ExpiresByType('text/plain', 'access plus 1 month');
        $this->assertEquals('text/plain "access plus 1 month"', $directive->value());
    }

    public function testExpiresByTypeWithModificationFormat(): void
    {
        $directive = new ExpiresByType('image/png', 'M86400');
        $this->assertEquals('image/png "M86400"', $directive->value());
    }

    public function testExpiresByTypeWithMultipleFormats(): void
    {
        $htmlDirective = new ExpiresByType('text/html', 'A3600');
        $cssDirective = new ExpiresByType('text/css', 'A604800');
        $jsDirective = new ExpiresByType('application/javascript', 'A2592000');

        $this->assertEquals('ExpiresByType text/html "A3600"', $htmlDirective->toString());
        $this->assertEquals('ExpiresByType text/css "A604800"', $cssDirective->toString());
        $this->assertEquals('ExpiresByType application/javascript "A2592000"', $jsDirective->toString());
    }
}
