<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\Header;

class HeaderTest extends TestCase
{
    #[DataProvider('renderCases')]
    public function testToString(Header $header, string $expected): void
    {
        $this->assertSame($expected, $header->toString());
    }

    public static function renderCases(): array
    {
        return [
            'set by default, single word left unquoted' => [new Header('X-Frame-Options', 'DENY'), 'Header set X-Frame-Options DENY'],
            'append, hyphenated value quoted' => [new Header('Cache-Control', 'no-cache', 'append'), 'Header append Cache-Control "no-cache"'],
            'unset drops the value' => [new Header('Server', 'Apache', 'unset'), 'Header unset Server'],
            'always' => [(new Header('X-Content-Type-Options', 'nosniff'))->withAlways(), 'Header always set X-Content-Type-Options nosniff'],
            'withVary() adds no token (1.0.9 regression)' => [(new Header('Cache-Control', '"gzip"', 'append'))->withVary(), 'Header append Cache-Control "gzip"'],
            'bare condition wrapped as env=' => [(new Header('Vary', 'Accept-Encoding', 'append'))->setCondition('!dont-vary'), 'Header append Vary "Accept-Encoding" env=!dont-vary'],
            'env= condition kept as is' => [(new Header('Vary', 'Accept-Encoding', 'append'))->setCondition('env=!dont-vary'), 'Header append Vary "Accept-Encoding" env=!dont-vary'],
            'expr= condition kept as is' => [(new Header('X-Test', 'value'))->setCondition('expr=%{REQUEST_URI} =~ /foo/'), 'Header set X-Test value expr=%{REQUEST_URI} =~ /foo/'],
            'empty condition adds nothing' => [(new Header('X-Test', 'value'))->setCondition(''), 'Header set X-Test value'],
            'always, quoted value, then condition' => [(new Header('Strict-Transport-Security', 'max-age=31536000'))->withAlways()->withVary()->setCondition('!dont-vary'), 'Header always set Strict-Transport-Security "max-age=31536000" env=!dont-vary'],
            'empty value quoted' => [new Header('X-Generator', ''), 'Header set X-Generator ""'],
            'already quoted value kept' => [new Header('X-Test', '"a b"'), 'Header set X-Test "a b"'],
            'forced comment' => [(new Header('X-Powered-By', 'MyApp/1.0'))->setForceComment(), '# Header set X-Powered-By "MyApp/1.0"'],
        ];
    }
}
