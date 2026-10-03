<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\HtPasswd;

class HtPasswdTest extends TestCase
{
    public function testToStringWithEmptyUsers(): void
    {
        $htpasswd = new HtPasswd();
        $output = $htpasswd->toString();
        $this->assertEquals('', $output);
    }

    public function testAddUser(): void
    {
        $htpasswd = new HtPasswd();
        $result = $htpasswd->addUser('testuser', 'testpass');

        $this->assertSame($htpasswd, $result); // Test fluent interface

        $output = $htpasswd->toString();
        $this->assertStringContainsString('testuser:', $output);
        $this->assertStringNotContainsString('testpass', $output);
    }

    public function testEncryptedPasswordGeneration(): void
    {
        $htpasswd = new HtPasswd();
        $htpasswd->addUser('user1', 'password123');

        $output = $htpasswd->toString();

        $this->assertStringContainsString('user1:', $output);
        $this->assertStringNotContainsString('password123', $output);
        $this->assertMatchesRegularExpression('/user1:\$apr1\$[a-zA-Z0-9\.\/]{8}\$[a-zA-Z0-9\.\/]{22}/', $output);
    }

    public function testMultipleUsers(): void
    {
        $htpasswd = new HtPasswd();
        $htpasswd->addUser('admin', 'admin123');
        $htpasswd->addUser('user', 'user456');

        $output = $htpasswd->toString();

        // Should contain both users
        $this->assertStringContainsString('admin:', $output);
        $this->assertStringContainsString('user:', $output);

        // Should never contain the clear-text passwords
        $this->assertStringNotContainsString('admin123', $output);
        $this->assertStringNotContainsString('user456', $output);

        // Should have proper line structure
        $lines = explode("\n", trim($output));
        $this->assertCount(2, $lines); // one name:hash line per user
    }

    public function testPasswordEncryptionConsistency(): void
    {
        $htpasswd1 = new HtPasswd();
        $htpasswd1->addUser('test', 'same_password');

        $htpasswd2 = new HtPasswd();
        $htpasswd2->addUser('test', 'same_password');

        $output1 = $htpasswd1->toString();
        $output2 = $htpasswd2->toString();

        // Different salts should produce different encrypted passwords
        $this->assertNotEquals($output1, $output2);

        // But both should be valid APR1-MD5 format
        $this->assertMatchesRegularExpression('/test:\$apr1\$[a-zA-Z0-9\.\/]{8}\$[a-zA-Z0-9\.\/]{22}/', $output1);
        $this->assertMatchesRegularExpression('/test:\$apr1\$[a-zA-Z0-9\.\/]{8}\$[a-zA-Z0-9\.\/]{22}/', $output2);
    }

    public function testSpecialCharactersInPassword(): void
    {
        $htpasswd = new HtPasswd();
        $specialPassword = 'p@$$w0rd!@#$%^&*()';
        $htpasswd->addUser('special', $specialPassword);

        $output = $htpasswd->toString();

        $this->assertStringContainsString('special:', $output);
        $this->assertStringNotContainsString($specialPassword, $output);
        $this->assertMatchesRegularExpression('/special:\$apr1\$[a-zA-Z0-9\.\/]{8}\$[a-zA-Z0-9\.\/]{22}/', $output);
    }

    public function testEmptyPassword(): void
    {
        $htpasswd = new HtPasswd();
        $htpasswd->addUser('emptypass', '');

        $output = $htpasswd->toString();

        $this->assertStringContainsString('emptypass:', $output);
        $this->assertStringNotContainsString('#', $output);
        $this->assertMatchesRegularExpression('/emptypass:\$apr1\$[a-zA-Z0-9\.\/]{8}\$[a-zA-Z0-9\.\/]{22}/', $output);
    }

    public function testUserWithSpecialCharactersInName(): void
    {
        $htpasswd = new HtPasswd();
        $htpasswd->addUser('user.name@domain.com', 'password');

        $output = $htpasswd->toString();

        $this->assertStringContainsString('user.name@domain.com:', $output);
        $this->assertStringNotContainsString('#', $output);
    }
}
