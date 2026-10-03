<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\FileETag;

class FileETagTest extends DirectiveTest
{
    public function testFileETagWithNone(): void
    {
        $directive = new FileETag('None');
        $this->assertEquals('None', $directive->value());
    }

    public function testFileETagToString(): void
    {
        $directive = new FileETag('None');
        $this->assertEquals('FileETag None', $directive->toString());
    }

    public function testFileETagWithMultiplePrefixes(): void
    {
        $directive = new FileETag('+MTime -Size');
        $this->assertEquals('+MTime -Size', $directive->value());
    }
}
