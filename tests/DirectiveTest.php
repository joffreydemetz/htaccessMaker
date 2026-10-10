<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive;
use JDZ\HtaccessMaker\EmptyLine;
use JDZ\HtaccessMaker\Directive\ServerSignature;
use JDZ\HtaccessMaker\Directive\ValueDirective;

/**
 * The rendering every directive shares: name + value, indentation, forced comment.
 * Each directive's own value formatting is tested in tests/Directive/.
 */
class DirectiveTest extends TestCase
{
    #[DataProvider('renderCases')]
    public function testToString(Directive $directive, bool $showComments, int $indent, string $expected): void
    {
        $this->assertSame($expected, $directive->toString($showComments, $indent));
    }

    public static function renderCases(): array
    {
        return [
            'name and value' => [new ServerSignature('Off'), true, 0, 'ServerSignature Off'],
            'hiding comments leaves a directive alone' => [new ServerSignature('Off'), false, 0, 'ServerSignature Off'],
            'indent 1' => [new ServerSignature('Off'), true, 1, '  ServerSignature Off'],
            'indent 3' => [new ServerSignature('Off'), true, 3, '      ServerSignature Off'],
            'forced comment' => [(new ServerSignature('Off'))->setForceComment(), true, 0, '# ServerSignature Off'],
            'forced comment survives hidden comments' => [(new ServerSignature('Off'))->setForceComment(), false, 0, '# ServerSignature Off'],
            'forced comment, indented' => [(new ServerSignature('Off'))->setForceComment(), true, 2, '    # ServerSignature Off'],
            'forced comment switched off again' => [(new ServerSignature('Off'))->setForceComment()->setForceComment(false), true, 0, 'ServerSignature Off'],
            'no name and no value' => [new Directive(), true, 0, ''],
            'EmptyLine renders nothing by itself' => [new EmptyLine(), true, 0, ''],
        ];
    }

    public function testSetNameNamesABareValueDirective(): void
    {
        $directive = new ValueDirective('Off');
        $directive->setName('ServerSignature');

        $this->assertSame('ServerSignature Off', $directive->toString());
    }
}
