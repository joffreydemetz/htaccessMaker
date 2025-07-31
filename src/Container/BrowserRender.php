<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker\Container;

use JDZ\HtaccessMaker\Container;
use JDZ\HtaccessMaker\Directive\Header;

/**
 * Browser Rendering
 * 
 * This container is responsible for setting the appropriate headers
 * for browser rendering of HTML and PHP files.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class BrowserRender extends Container
{
    public function process(array $config = []): void
    {
        if (false === ($config = $this->parseConfig($config))) {
            return;
        }

        $filesMatch = new FilesMatch();
        $filesMatch->process(['pattern' => '.(php|html)$']);
        $filesMatch->addDirective(new Header('Content-Style-Type', 'text/css', 'set'));
        $filesMatch->addDirective(new Header('Content-Script-Type', 'text/javascript', 'set'));
        $this->addDirective($filesMatch);
    }
}
