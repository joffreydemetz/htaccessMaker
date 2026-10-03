<?php

declare(strict_types=1);

namespace Tests\Directive;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Directive\Comment;

class CommentTest extends TestCase
{
    public function testSimpleComment(): void
    {
        $comment = new Comment('This is a test comment');

        $output = $comment->toString(true);
        $this->assertStringContainsString('# This is a test comment', $output);
    }

    public function testCommentWithBorder(): void
    {
        $comment = new Comment('Bordered comment', '####################');

        $output = $comment->toString(true);

        $this->assertStringContainsString('####################', $output);
        $this->assertStringContainsString('# Bordered comment', $output);

        // Should contain the border twice (before and after)
        $this->assertEquals(2, substr_count($output, '####################'));
    }

    public function testCommentWithIndentation(): void
    {
        $comment = new Comment('Indented comment');

        $output = $comment->toString(true, 2);
        $this->assertStringContainsString('    # Indented comment', $output);
    }

    public function testCommentWithBorderAndIndentation(): void
    {
        $comment = new Comment('Test', '###');

        $output = $comment->toString(true, 1);
        $lines = explode("\n", $output);

        // Check that all non-empty lines are properly indented
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $this->assertStringStartsWith('  ', $line); // 2 spaces (1 * 2)
            }
        }

        $this->assertStringContainsString('  ###', $output);
        $this->assertStringContainsString('  # Test', $output);
    }

    public function testCommentHiddenWhenCommentsOff(): void
    {
        $comment = new Comment('Hidden comment');
        $this->assertEquals('', $comment->toString(false));
    }

    public function testCommentWithForceCommentStillShows(): void
    {
        $comment = new Comment('Forced comment');
        $comment->setForceComment(true);

        $this->assertStringContainsString('# Forced comment', $comment->toString(true));
        $this->assertStringContainsString('# Forced comment', $comment->toString(false));
    }

    public function testEmptyComment(): void
    {
        $comment = new Comment('');

        $output = $comment->toString(true);
        $this->assertStringContainsString('', $output);
    }

    public function testCommentWithSpecialCharacters(): void
    {
        $comment = new Comment('Comment with @#$%^&*() special chars!');

        $output = $comment->toString(true);
        $this->assertStringContainsString('# Comment with @#$%^&*() special chars!', $output);
    }

    public function testCommentWithNewlines(): void
    {
        $comment = new Comment("Multi\nline\ncomment");

        $output = $comment->toString(true);
        $this->assertStringContainsString("# Multi", $output);
        $this->assertStringContainsString("# line", $output);
        $this->assertStringContainsString("# comment", $output);
    }

    public function testCommentBorderOnly(): void
    {
        $comment = new Comment('Test', '=====');

        $output = $comment->toString(true);

        $this->assertStringContainsString('=====', $output);
        $this->assertStringContainsString('# Test', $output);

        $this->assertEquals('' . "\n" . '=====' . "\n" . '# Test' . "\n" . '=====' . "\n" . "\n", $output);
        // Should have structure: empty line, border, comment, border, empty line
        $lines = explode("\n", $output);
        $this->assertCount(6, $lines);
        $this->assertEquals('', $lines[0]); // Empty line
        $this->assertEquals('=====', $lines[1]); // Border
        $this->assertEquals('# Test', $lines[2]); // Comment
        $this->assertEquals('=====', $lines[3]); // Border
        $this->assertEquals('', $lines[4]); // Empty line
    }
}
