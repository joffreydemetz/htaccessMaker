<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\FileETag;

class FileETagTest extends TestCase
{
    public function testFileETagToString(): void
    {
        $directive = new FileETag('None');

        $this->assertSame('FileETag None', $directive->toString());
    }
}
