<?php
/*--------------------------------------------------------------
   HistoryFactory.php 2021-04-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Factories;

use DateTime;
use Gambio\Shop\UserNavigationHistory\Entities\HistoryEntry;
use Gambio\Shop\UserNavigationHistory\ValueObjects\CatParameter;
use Gambio\Shop\UserNavigationHistory\ValueObjects\Uri;

/**
 * Class HistoryFactory
 * @package Gambio\Shop\UserNavigationHistory\Factories
 */
class HistoryFactory
{
    /**
     * @param string $uriValue
     * @param array  $getParameters
     *
     * @return HistoryEntry
     */
    public function createHistoryEntry(
        string $uriValue,
        array $getParameters
    ): HistoryEntry {
        
        return new HistoryEntry(new Uri($uriValue), $getParameters, new DateTime);
    }
}