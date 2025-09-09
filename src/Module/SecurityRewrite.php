<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Module;

use JDZ\HtaccessMaker\Module\RewriteModule;
use JDZ\HtaccessMaker\Directive\Comment;

/**
 * Security Rewrite
 * 
 * Handles security-related URL rewriting and blocking.
 * Provides methods to block URL attacks, SQL injection, shell injection,
 * block malicious requests, and more.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class SecurityRewrite extends RewriteModule
{
    public function process(array $config = []): void
    {
        $config = array_merge([
            'blockUrlAttacks' => true,
            'blockSqlInjection' => true,
            'blockShellInjection' => true,
            'blockMaliciousRequests' => true,
            'blacklistHttpMethods' => ['HEAD', 'TRACE', 'TRACK', 'OPTIONS', 'HEAD', 'PUT', 'DELETE'],
            'blacklistUserAgents' => [],
            'blacklistReferrers' => [],
            'blockAction' => '/index.php',
            'blockFlags' => ['R=403', 'L'],
        ], $config);

        if ($config['blockUrlAttacks']) {
            $this->addUrlAttackBlocking($config['blockAction'], $config['blockFlags']);
        }

        if ($config['blockSqlInjection']) {
            $this->addSqlInjectionBlocking($config['blockAction'], $config['blockFlags']);
        }

        if ($config['blockShellInjection']) {
            $this->addShellInjectionBlocking($config['blockAction'], $config['blockFlags']);
        }

        if (!empty($config['blacklistHttpMethods'])) {
            $config['blacklistHttpMethods'] = array_unique($config['blacklistHttpMethods']);
            $this->addHttpMethodBlocking($config['blacklistHttpMethods'], $config['blockAction'], $config['blockFlags']);
        }

        if ($config['blacklistUserAgents']) {
            $this->addUserAgentBlocking($config['blacklistUserAgents'], true, $config['blockAction'], $config['blockFlags']);
        }

        if ($config['blacklistReferrers']) {
            $this->addReferrerBlocking($config['blacklistReferrers'], $config['blockAction'], $config['blockFlags']);
        }

        if ($config['blockMaliciousRequests']) {
            $this->addRequestBlocking($config['blockAction'], $config['blockFlags']);
        }
    }

    public function addUrlAttackBlocking(string $blockAction = 'index.php', array $flags = ['F']): self
    {
        //$this->addDirective(new Comment('Block URL attacks'));

        // Block base64_encode attempts
        $this->addDirective(new Comment('Block out any script trying to base64_encode data within the URL.'));
        $this->addRewriteCond('%{QUERY_STRING}', 'base64_encode[^(]*\([^)]*\)', ['OR']);

        // Block script tags in URL
        $this->addDirective(new Comment('Block out any script that includes a <script> tag in URL.'));
        $this->addRewriteCond('%{QUERY_STRING}', '(<|%3C)([^s]*s)+cript.*(>|%3E)', ['NC', 'OR']);

        // Block GLOBALS variable manipulation
        $this->addDirective(new Comment('Block out any script trying to set a PHP GLOBALS variable via URL.'));
        $this->addRewriteCond('%{QUERY_STRING}', 'GLOBALS(=|\[|\%[0-9A-Z]{0,2})', ['OR']);

        // Block _REQUEST variable manipulation
        $this->addDirective(new Comment('Block out any script trying to modify a _REQUEST variable via URL.'));
        $this->addRewriteCond('%{QUERY_STRING}', '_REQUEST(=|\[|\%[0-9A-Z]{0,2})');

        // Block action
        $this->addDirective(new Comment('Return 403 Forbidden header and show the content of the root homepage'));
        $this->addRewriteRule('.*', $blockAction, $flags);

        // $this->addDirective(new Comment('END Block URL attacks'));

        return $this;
    }

    public function addSqlInjectionBlocking(string $blockAction = 'index.php', array $flags = ['F']): self
    {
        $this->addDirective(new Comment('Block SQL injection attacks'));

        // Block SQL injection patterns in query string
        $this->addRewriteCond('%{QUERY_STRING}', '(;|<|>|\'|"|\)|%0A|%0D|%22|%27|%3C|%3E|%00).*(/\*|union|select|insert|cast|set|declare|drop|update|md5|benchmark)', ['NC', 'OR']);

        // Block localhost/loopback references
        $this->addRewriteCond('%{QUERY_STRING}', '(localhost|loopback|127\.0\.0\.1)', ['NC', 'OR']);

        // Block common SQL keywords
        $this->addRewriteCond('%{QUERY_STRING}', '(alter|create|delete|drop|exec|execute|insert|select|union|update)', ['NC']);

        $this->addRewriteRule('.*', $blockAction, $flags);
        //$this->addDirective(new Comment('END SQL injection blocking'));

        return $this;
    }

    public function addShellInjectionBlocking(string $blockAction = 'index.php', array $flags = ['F']): self
    {
        $this->addDirective(new Comment('Block shell injection and file upload attacks'));

        // Block shell uploaders and backdoors
        $this->addRewriteCond('%{REQUEST_URI}', '((php|my|bypass)?shell|remview.*|phpremoteview.*|sshphp.*|pcom|nstview.*|c99|c100|r57|webadmin.*|phpget.*|phpwriter.*|fileditor.*|locus7.*|storm7.*)', ['NC', 'OR']);

        // Block dangerous file extensions and commands
        $this->addRewriteCond('%{REQUEST_URI}', '(\.exe|\.tar|_vti|afilter=|algeria\.php|chbd|chmod|cmd|command|db_query|download_file|echo|edit_file|eval|evil_root|exploit)', ['NC', 'OR']);

        // Block more dangerous patterns
        $this->addRewriteCond('%{REQUEST_URI}', '(find_text|fopen|fsbuff|fwrite|friends_links\.|ftp|gofile|grab|grep|htshell|lynx|mail_file|md5|mkdir|mkfile|mkmode)', ['NC', 'OR']);

        // Block system commands
        $this->addRewriteCond('%{REQUEST_URI}', '(passthru|popen|proc_open|processes|pwd|rmdir|root|safe0ver|search_text|selfremove|setup\.php|shell|system|telnet|trojan|uname|unzip|whoami|xampp)', ['NC']);

        $this->addRewriteRule('.*', $blockAction, $flags);
        //$this->addDirective(new Comment('END Shell injection blocking'));

        return $this;
    }

    public function addHttpMethodBlocking(array $blockedMethods = ['HEAD', 'TRACE', 'TRACK'], string $blockAction = 'index.php', array $flags = ['F']): self
    {
        $this->addDirective(new Comment('Block dangerous HTTP methods'));

        $methodPattern = '^(' . implode('|', $blockedMethods) . ')';
        $this->addRewriteCond('%{REQUEST_METHOD}', $methodPattern, ['NC']);
        $this->addRewriteRule('.*', $blockAction, $flags);

        // $this->addDirective(new Comment('END HTTP method blocking'));

        return $this;
    }

    public function addReferrerBlocking(array $blockedReferrers = [], string $blockAction = 'index.php', array $flags = ['F']): self
    {
        $this->addDirective(new Comment('Block malicious referrers'));

        // Block referrers with illegal characters
        $this->addRewriteCond('%{HTTP_REFERER}', '(<|>|\'|%0A|%0D|%27|%3C|%3E|%00)', ['NC', 'OR']);

        // Block specific referrers if provided
        foreach ($blockedReferrers as $referrer) {
            $this->addRewriteCond('%{HTTP_REFERER}', $referrer, ['NC', 'OR']);
        }

        // Block common spam referrers
        $this->addRewriteCond('%{HTTP_REFERER}', '(semalt|kambasoft|savetubevideo|buttons-for-website|aliexpress)', ['NC']);

        $this->addRewriteRule('.*', $blockAction, $flags);
        //$this->addDirective(new Comment('END Referrer blocking'));

        return $this;
    }

    public function addRequestBlocking(string $blockAction = 'index.php', array $flags = ['F']): self
    {
        $this->addDirective(new Comment('Block malicious request patterns'));

        // Block requests with illegal characters in THE_REQUEST
        $this->addRewriteCond('%{THE_REQUEST}', '(\\r|\\n|%0A|%0D)', ['NC', 'OR']);

        // Block malformed URIs
        $this->addRewriteCond('%{REQUEST_URI}', '^/(,|;|:|<|>|"|>|"<|/|\\\.\.\\)', ['NC', 'OR']);

        // Block directory traversal attempts
        $this->addRewriteCond('%{REQUEST_URI}', '(\.\./|\.\.\|%2e%2e)', ['NC', 'OR']);

        // Block null bytes and other dangerous characters
        $this->addRewriteCond('%{REQUEST_URI}', '(%00|%08|%09|%0a|%0b|%0c|%0d)', ['NC']);

        $this->addRewriteRule('.*', $blockAction, $flags);
        //$this->addDirective(new Comment('END Request blocking'));

        return $this;
    }

    public function addUserAgentBlocking(array $blockedAgents = [], bool $blockEmpty = true, string $blockAction = 'index.php', array $flags = ['F']): self
    {
        $this->addDirective(new Comment('Block malicious user agents'));

        if ($blockEmpty) {
            $this->addDirective(new Comment('Block empty user agent strings'));
            $this->addRewriteCond('%{HTTP_USER_AGENT}', '^$', ['OR']);
        }

        // Block user agents with illegal characters
        $this->addRewriteCond('%{HTTP_USER_AGENT}', '(<|>|\'|%0A|%0D|%27|%3C|%3E|%00)', ['NC', 'OR']);

        // Block specific malicious user agents if provided
        foreach ($blockedAgents as $agent) {
            $this->addRewriteCond('%{HTTP_USER_AGENT}', $agent, ['NC', 'OR']);
        }

        // Remove the last OR if we have conditions
        if ($blockEmpty || !empty($blockedAgents)) {
            $this->addRewriteCond('%{HTTP_USER_AGENT}', '(bot|crawler|spider|scraper)', ['NC']);
            $this->addRewriteRule('.*', $blockAction, $flags);
        }

        // $this->addDirective(new Comment('END User agent blocking'));

        return $this;
    }
}
