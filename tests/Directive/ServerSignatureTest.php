<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\ServerSignature;

class ServerSignatureTest extends TestCase
{
    public function testServerSignatureOff(): void
    {
        $directive = new ServerSignature('Off');
        $this->assertEquals('ServerSignature Off', $directive->toString());
    }
}
