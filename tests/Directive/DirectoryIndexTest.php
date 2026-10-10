<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\DirectoryIndex;

class DirectoryIndexTest extends TestCase
{
    public function testDirectoryIndexWithSingleFile(): void
    {
        $directive = new DirectoryIndex('index.html');

        $this->assertSame('DirectoryIndex index.html', $directive->toString());
    }
}
