<?php

declare(strict_types=1);

namespace Tests\Module;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\RedirectWwwRewrite;

class RedirectWwwRewriteTest extends TestCase
{
    public function testRedirectWwwRewrite(): void
    {
        $module = new RedirectWwwRewrite();
        $module->process(['enabled' => true]);

        $this->assertSame(implode("\n", [
            'RewriteEngine On',
            '# Redirect www to non-www',
            'RewriteCond %{HTTP_HOST} ^www\.(.*)$',
            'RewriteRule ^(.*)$ https://%1/$1 [R=301,L]',
            '',
            '',
        ]), $module->toString());
    }

    public function testEmptyConfigAddsNothing(): void
    {
        $module = new RedirectWwwRewrite();
        $module->process();

        $this->assertSame("RewriteEngine On\n\n", $module->toString());
    }
}
