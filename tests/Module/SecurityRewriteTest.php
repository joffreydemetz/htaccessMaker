<?php

declare(strict_types=1);

namespace Tests\Module;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use JDZ\HtaccessMaker\Module\SecurityRewrite;

class SecurityRewriteTest extends TestCase
{
    private const URL_ATTACKS = [
        '# Block out any script trying to base64_encode data within the URL.',
        'RewriteCond %{QUERY_STRING} base64_encode[^(]*\([^)]*\) [OR]',
        '# Block out any script that includes a <script> tag in URL.',
        'RewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]',
        '# Block out any script trying to set a PHP GLOBALS variable via URL.',
        'RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]',
        '# Block out any script trying to modify a _REQUEST variable via URL.',
        'RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})',
        '# Return 403 Forbidden header and show the content of the root homepage',
    ];

    private const SQL_INJECTION = [
        '# Block SQL injection attacks',
        'RewriteCond %{QUERY_STRING} (;|<|>|\'|"|\)|%0A|%0D|%22|%27|%3C|%3E|%00).*(/\*|union|select|insert|cast|set|declare|drop|update|md5|benchmark) [NC,OR]',
        'RewriteCond %{QUERY_STRING} (localhost|loopback|127\.0\.0\.1) [NC,OR]',
        'RewriteCond %{QUERY_STRING} (alter|create|delete|drop|exec|execute|insert|select|union|update) [NC]',
    ];

    private const SHELL_INJECTION = [
        '# Block shell injection and file upload attacks',
        'RewriteCond %{REQUEST_URI} ((php|my|bypass)?shell|remview.*|phpremoteview.*|sshphp.*|pcom|nstview.*|c99|c100|r57|webadmin.*|phpget.*|phpwriter.*|fileditor.*|locus7.*|storm7.*) [NC,OR]',
        'RewriteCond %{REQUEST_URI} (\.exe|\.tar|_vti|afilter=|algeria\.php|chbd|chmod|cmd|command|db_query|download_file|echo|edit_file|eval|evil_root|exploit) [NC,OR]',
        'RewriteCond %{REQUEST_URI} (find_text|fopen|fsbuff|fwrite|friends_links\.|ftp|gofile|grab|grep|htshell|lynx|mail_file|md5|mkdir|mkfile|mkmode) [NC,OR]',
        'RewriteCond %{REQUEST_URI} (passthru|popen|proc_open|processes|pwd|rmdir|root|safe0ver|search_text|selfremove|setup\.php|shell|system|telnet|trojan|uname|unzip|whoami|xampp) [NC]',
    ];

    /**
     * @param Closure(SecurityRewrite): mixed $add
     */
    #[DataProvider('blockCases')]
    public function testAddBlock(Closure $add, string $expected): void
    {
        $module = new SecurityRewrite();
        $add($module);

        $this->assertSame($expected, $module->toString());
    }

    public static function blockCases(): array
    {
        return [
            'URL attacks, default action and flag' => [
                static fn(SecurityRewrite $m) => $m->addUrlAttackBlocking(),
                self::lines(...self::URL_ATTACKS, ...['RewriteRule .* index.php [F]']),
            ],
            'URL attacks, own action' => [
                static fn(SecurityRewrite $m) => $m->addUrlAttackBlocking('/blocked.html'),
                self::lines(...self::URL_ATTACKS, ...['RewriteRule .* /blocked.html [F]']),
            ],
            'SQL injection, own action and flags' => [
                static fn(SecurityRewrite $m) => $m->addSqlInjectionBlocking('/error.html', ['R=403', 'L']),
                self::lines(...self::SQL_INJECTION, ...['RewriteRule .* /error.html [R=403,L]']),
            ],
            'shell injection' => [
                static fn(SecurityRewrite $m) => $m->addShellInjectionBlocking(),
                self::lines(...self::SHELL_INJECTION, ...['RewriteRule .* index.php [F]']),
            ],
            'HTTP methods, default list' => [
                static fn(SecurityRewrite $m) => $m->addHttpMethodBlocking(),
                self::lines('# Block dangerous HTTP methods', 'RewriteCond %{REQUEST_METHOD} ^(HEAD|TRACE|TRACK) [NC]', 'RewriteRule .* index.php [F]'),
            ],
            'HTTP methods, own list' => [
                static fn(SecurityRewrite $m) => $m->addHttpMethodBlocking(['PUT', 'DELETE']),
                self::lines('# Block dangerous HTTP methods', 'RewriteCond %{REQUEST_METHOD} ^(PUT|DELETE) [NC]', 'RewriteRule .* index.php [F]'),
            ],
            'referrers: illegal characters, the given ones, the spam list' => [
                static fn(SecurityRewrite $m) => $m->addReferrerBlocking(['spam\.example']),
                self::lines(
                    '# Block malicious referrers',
                    'RewriteCond %{HTTP_REFERER} (<|>|\'|%0A|%0D|%27|%3C|%3E|%00) [NC,OR]',
                    'RewriteCond %{HTTP_REFERER} spam\.example [NC,OR]',
                    'RewriteCond %{HTTP_REFERER} (semalt|kambasoft|savetubevideo|buttons-for-website|aliexpress) [NC]',
                    'RewriteRule .* index.php [F]',
                ),
            ],
        ];
    }

    /**
     * Default config minus the request blocking (its pattern does not compile, reported).
     * The default method list names HEAD twice: it is rendered once.
     */
    public function testProcessDefaultsWithoutRequestBlocking(): void
    {
        $module = new SecurityRewrite();
        $module->process(['blockMaliciousRequests' => false]);

        $this->assertSame(self::lines(
            ...self::URL_ATTACKS,
            ...['RewriteRule .* /index.php [R=403,L]'],
            ...self::SQL_INJECTION,
            ...['RewriteRule .* /index.php [R=403,L]'],
            ...self::SHELL_INJECTION,
            ...[
                'RewriteRule .* /index.php [R=403,L]',
                '# Block dangerous HTTP methods',
                'RewriteCond %{REQUEST_METHOD} ^(HEAD|TRACE|TRACK|OPTIONS|PUT|DELETE) [NC]',
                'RewriteRule .* /index.php [R=403,L]',
            ],
        ), $module->toString());
    }

    public function testProcessWithEveryBlockOff(): void
    {
        $module = new SecurityRewrite();
        $module->process([
            'blockUrlAttacks' => false,
            'blockSqlInjection' => false,
            'blockShellInjection' => false,
            'blockMaliciousRequests' => false,
            'blacklistHttpMethods' => [],
        ]);

        $this->assertSame("RewriteEngine On\n\n", $module->toString());
    }

    // The next three only check fragments on purpose: addUserAgentBlocking() and
    // addRequestBlocking() render lines that are reported as bugs, not pinned.

    public function testSecurityRewriteWithUserAgentBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addUserAgentBlocking(['badbot', 'scraper']);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block malicious user agents', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_USER_AGENT}', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithRequestBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addRequestBlocking();

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block malicious request patterns', $output);
        $this->assertStringContainsString('RewriteCond', $output);
        $this->assertStringContainsString('%00|%08|%09', $output);
        $this->assertStringContainsString('[F]', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    public function testSecurityRewriteWithEmptyUserAgentBlocking(): void
    {
        $container = new SecurityRewrite();
        $container->ensureApacheCompatibility();
        $container->addUserAgentBlocking([], true);

        $output = $container->toString();

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $output);
        $this->assertStringContainsString('RewriteEngine On', $output);
        $this->assertStringContainsString('# Block empty user agent strings', $output);
        $this->assertStringContainsString('RewriteCond %{HTTP_USER_AGENT} ^$', $output);
        $this->assertStringContainsString('</IfModule>', $output);
    }

    /**
     * The module's rendering: RewriteEngine On, the given lines, a closing blank line.
     */
    private static function lines(string ...$lines): string
    {
        return implode("\n", ['RewriteEngine On', ...$lines, '', '']);
    }
}
