<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\DirectoryIndex;

class DirectoryIndexTest extends DirectiveTest
{
    public function testDirectoryIndexWithSingleFile(): void
    {
        $directive = new DirectoryIndex('index.html');
        $this->assertStringContainsString('DirectoryIndex index.html', $directive->toString());
    }

    public function testDirectoryIndexWithMultipleFiles(): void
    {
        $directive = new DirectoryIndex('index.html index.php default.html');
        $this->assertStringContainsString('DirectoryIndex index.html index.php default.html', $directive->toString());
    }
}
