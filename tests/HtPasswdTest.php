<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\HtPasswd;

class HtPasswdTest extends TestCase
{
    public function testToStringWithEmptyUsers(): void
    {
        $this->assertSame('', (new HtPasswd())->toString());
    }

    public function testEncryptedPasswordGeneration(): void
    {
        $htpasswd = new HtPasswd();
        $htpasswd->addUser('user1', 'password123');

        $this->assertMatchesRegularExpression('/^user1:\$apr1\$[.\/0-9A-Za-z]{8}\$[.\/0-9A-Za-z]{22}\n$/D', $htpasswd->toString());
    }

    public function testMultipleUsers(): void
    {
        $htpasswd = new HtPasswd();
        $htpasswd->addUser('admin', 'admin123');
        $htpasswd->addUser('user', 'user456');

        $this->assertMatchesRegularExpression(
            '/^admin:\$apr1\$[.\/0-9A-Za-z]{8}\$[.\/0-9A-Za-z]{22}\nuser:\$apr1\$[.\/0-9A-Za-z]{8}\$[.\/0-9A-Za-z]{22}\n$/D',
            $htpasswd->toString()
        );
    }

    public function testSamePasswordGetsAFreshSalt(): void
    {
        $first = (new HtPasswd())->addUser('test', 'same_password')->toString();
        $second = (new HtPasswd())->addUser('test', 'same_password')->toString();

        $this->assertNotSame($first, $second);
    }

    /**
     * The salt is random, so the hash is checked against an independent APR1-MD5
     * implementation (self::apr1()), itself pinned to `openssl passwd -apr1 -salt` vectors.
     */
    #[DataProvider('passwords')]
    public function testHashIsApr1Md5(string $password, string $vectorSalt, string $vectorHash): void
    {
        $this->assertSame($vectorHash, self::apr1($password, $vectorSalt), 'reference implementation against openssl');

        $line = (new HtPasswd())->addUser('user', $password)->toString();

        $this->assertSame(1, preg_match('/^user:(\$apr1\$([.\/0-9A-Za-z]{8})\$[.\/0-9A-Za-z]{22})\n$/D', $line, $match), $line);
        $this->assertSame(self::apr1($password, $match[2]), $match[1]);
    }

    public static function passwords(): array
    {
        return [
            'short' => ['password', 'abcdefgh', '$apr1$abcdefgh$FBwExRW4dCc8aL.OvjpIE1'],
            'empty' => ['', '0123abcd', '$apr1$0123abcd$5CfKTUzx/RwoJidpYir71/'],
            'special characters' => ['p@$$w0rd!@#$%^&*()', 'zz9yy8xx', '$apr1$zz9yy8xx$GDttPOr9Z7ywU6tYRHXiY.'],
            'longer than one MD5 digest' => ['a-much-longer-password-than-sixteen-bytes', 'q1w2e3r4', '$apr1$q1w2e3r4$qoniD.8r5LPIUFs7UB4ll/'],
        ];
    }

    /**
     * APR1-MD5 as in apr_md5.c (md5crypt with the "$apr1$" magic and its to64 encoding).
     */
    private static function apr1(string $password, string $salt): string
    {
        $context = $password . '$apr1$' . $salt;
        $final = md5($password . $salt . $password, true);
        for ($left = strlen($password); $left > 0; $left -= 16) {
            $context .= substr($final, 0, min(16, $left));
        }
        for ($i = strlen($password); $i > 0; $i >>= 1) {
            $context .= ($i & 1) ? "\0" : $password[0];
        }
        $final = md5($context, true);

        for ($i = 0; $i < 1000; $i++) {
            $round = ($i & 1) ? $password : $final;
            if ($i % 3) {
                $round .= $salt;
            }
            if ($i % 7) {
                $round .= $password;
            }
            $round .= ($i & 1) ? $final : $password;
            $final = md5($round, true);
        }

        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $bytes = array_values(unpack('C*', $final));
        $groups = [[0, 6, 12, 4], [1, 7, 13, 4], [2, 8, 14, 4], [3, 9, 15, 4], [4, 10, 5, 4]];
        $hash = '';
        foreach ($groups as [$a, $b, $c, $chars]) {
            $value = ($bytes[$a] << 16) | ($bytes[$b] << 8) | $bytes[$c];
            for ($n = 0; $n < $chars; $n++, $value >>= 6) {
                $hash .= $itoa64[$value & 0x3f];
            }
        }
        for ($n = 0, $value = $bytes[11]; $n < 2; $n++, $value >>= 6) {
            $hash .= $itoa64[$value & 0x3f];
        }

        return '$apr1$' . $salt . '$' . $hash;
    }
}
