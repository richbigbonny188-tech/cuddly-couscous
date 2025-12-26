<?php
/*--------------------------------------------------------------
   OnSet404HeaderEventListener.php 2021-01-25
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\OnSet404HeaderEvent;

/**
 * Class OnSet404HeaderEventListener
 * @package Gambio\Shop\SellingUnit\Database\Unit\Listener
 * @codeCoverageIgnore
 */
class OnSet404HeaderEventListener
{
    /**
     * @param OnSet404HeaderEvent $event
     */
    public function __invoke(OnSet404HeaderEvent $event)
    {
        if (!headers_sent()) {
            
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('HTTP/1.0 404 Not Found');
        }
    }
}