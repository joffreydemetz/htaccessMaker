<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive;
use JDZ\HtaccessMaker\Directive\Comment;
use JDZ\HtaccessMaker\Directive\ServerSignature;

class DirectiveTest extends TestCase
{
    public function testForceComment(): void
    {
        $directive = new ServerSignature('Off');
        $directive->setForceComment(true);

        $this->assertStringStartsWith('# ServerSignature Off', $directive->toString());
        $this->assertStringStartsWith('# ServerSignature Off', $directive->toString(false));
    }

    public function testWithoutForceComment(): void
    {
        $directive = new ServerSignature('Off');

        $this->assertStringStartsWith('ServerSignature Off', $directive->toString());
        $this->assertStringStartsWith('ServerSignature Off', $directive->toString(false));
    }

    public function testIndentation(): void
    {
        $directive = new ServerSignature('Off');

        $this->assertStringStartsWith('ServerSignature Off', $directive->toString(false));
        $this->assertStringStartsWith('  ServerSignature Off', $directive->toString(false, 1));
        $this->assertStringStartsWith('    ServerSignature Off', $directive->toString(false, 2));
        $this->assertStringStartsWith('      ServerSignature Off', $directive->toString(false, 3));
    }
}
