<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\Comment;

class CommentTest extends TestCase
{
    #[DataProvider('renderCases')]
    public function testToString(Comment $comment, bool $showComments, int $indent, string $expected): void
    {
        $this->assertSame($expected, $comment->toString($showComments, $indent));
    }

    public static function renderCases(): array
    {
        return [
            'simple' => [new Comment('This is a test comment'), true, 0, '# This is a test comment'],
            'indented' => [new Comment('Indented comment'), true, 2, '    # Indented comment'],
            'hidden when comments are off' => [new Comment('Hidden comment'), false, 0, ''],
            'forced, comments on' => [(new Comment('Forced comment'))->setForceComment(), true, 0, '# Forced comment'],
            'forced, comments off' => [(new Comment('Forced comment'))->setForceComment(), false, 0, '# Forced comment'],
            'one line per line, trimmed, blank lines dropped' => [new Comment("  Multi \n\nline\ncomment  "), true, 0, "# Multi\n# line\n# comment"],
            'border: blank line, border, comment, border, blank line' => [new Comment('Test', '====='), true, 0, "\n=====\n# Test\n=====\n\n"],
            'border, indented' => [new Comment('Test', '###'), true, 1, "  \n  ###\n  # Test\n  ###\n  \n"],
            'empty' => [new Comment(''), true, 0, ''],
            'blank lines only' => [new Comment("  \n \n"), true, 0, ''],
        ];
    }
}
