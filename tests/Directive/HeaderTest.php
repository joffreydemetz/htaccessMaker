<?php

declare(strict_types=1);

namespace Tests\Directive;

use Tests\DirectiveTest;
use JDZ\HtaccessMaker\Directive\Header;

class HeaderTest extends DirectiveTest
{
    public function testHeaderWithSimpleValue(): void
    {
        $directive = new Header('X-Frame-Options', 'DENY');
        $this->assertStringContainsString('Header set X-Frame-Options DENY', $directive->toString());
    }

    public function testHeaderWithAppendAction(): void
    {
        $directive = new Header('Cache-Control', 'no-cache', 'append');
        $this->assertStringContainsString('Header append Cache-Control "no-cache"', $directive->toString());
    }

    public function testHeaderWithUnsetAction(): void
    {
        $directive = new Header('Server', null, 'unset');
        $this->assertStringContainsString('Header unset Server', $directive->toString());
    }

    public function testHeaderWithAlways(): void
    {
        $directive = new Header('X-Content-Type-Options', 'nosniff');
        $directive->withAlways();

        $this->assertStringContainsString('Header always set X-Content-Type-Options nosniff', $directive->toString());
    }

    public function testHeaderWithVary(): void
    {
        // withVary() is a no-op: there is no `vary` token in Apache's Header grammar.
        $directive = new Header('Cache-Control', '"gzip"', 'append');
        $directive->withVary();

        $this->assertStringContainsString('Header append Cache-Control "gzip"', $directive->toString());
        $this->assertStringNotContainsString('vary', $directive->toString());
    }

    public function testHeaderWithVaryCondition(): void
    {
        $directive = new Header('Cache-Control', 'gzip', 'append');
        $directive->withVary();
        $directive->setCondition('!dont-vary');

        $this->assertStringContainsString('Header append Cache-Control gzip env=!dont-vary', $directive->toString());
    }

    public function testHeaderWithAlwaysAndVary(): void
    {
        $directive = new Header('Strict-Transport-Security', 'max-age=31536000');
        $directive->withAlways();
        $directive->withVary();

        $this->assertStringContainsString('Header always set Strict-Transport-Security "max-age=31536000"', $directive->toString());
        $this->assertStringNotContainsString('vary', $directive->toString());
    }

    public function testHeaderWithAlwaysAndVaryAndCondition(): void
    {
        $directive = new Header('Strict-Transport-Security', 'max-age=31536000');
        $directive->withAlways();
        $directive->withVary();
        $directive->setCondition('!dont-vary');

        $this->assertStringContainsString('Header always set Strict-Transport-Security "max-age=31536000" env=!dont-vary', $directive->toString());
    }

    public function testSetConditionWrapsBareConditionAsEnv(): void
    {
        $directive = new Header('Vary', 'Accept-Encoding', 'append');
        $directive->setCondition('!dont-vary');

        $this->assertStringContainsString('env=!dont-vary', $directive->toString());
    }

    public function testSetConditionLeavesEnvPrefixUntouched(): void
    {
        $directive = new Header('Vary', 'Accept-Encoding', 'append');
        $directive->setCondition('env=!dont-vary');

        $value = $directive->toString();
        $this->assertStringContainsString('env=!dont-vary', $value);
        $this->assertStringNotContainsString('env=env=', $value);
    }

    public function testSetConditionLeavesExprPrefixUntouched(): void
    {
        $directive = new Header('X-Test', 'value', 'set');
        $directive->setCondition('expr=%{REQUEST_URI} =~ /foo/');

        $value = $directive->toString();
        $this->assertStringContainsString('expr=%{REQUEST_URI} =~ /foo/', $value);
        $this->assertStringNotContainsString('env=expr=', $value);
    }

    public function testHeaderWithForceComment(): void
    {
        $directive = new Header('X-Powered-By', 'MyApp/1.0');
        $directive->setForceComment(true);

        $this->assertStringContainsString('# Header set X-Powered-By "MyApp/1.0"', $directive->toString(false));
    }

    public function testHeaderWithEmptyValue(): void
    {
        $directive = new Header('X-Generator', '');
        $this->assertStringContainsString('Header set X-Generator ""', $directive->toString());
    }
}
