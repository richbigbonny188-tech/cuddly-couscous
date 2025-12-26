<?php
/*--------------------------------------------------------------
   HistoryEntry.php 2021-04-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Entities;

use DateTime;
use Gambio\Shop\UserNavigationHistory\ValueObjects\Uri;

/**
 * Class HistoryEntry
 * @package Gambio\Shop\UserNavigationHistory\Entities
 */
class HistoryEntry
{
    /**
     * @var Uri
     */
    protected $uri;
    
    /**
     * @var string[]
     */
    protected $getParameters;
    
    /**
     * @var DateTime
     */
    protected $dateTime;
    
    
    /**
     * HistoryEntry constructor.
     *
     * @param Uri       $uri
     * @param string[]  $getParameters
     * @param DateTime $dateTime
     */
    public function __construct(
        Uri $uri,
        array $getParameters,
        DateTime $dateTime
    ) {
        $this->uri            = $uri;
        $this->getParameters  = $getParameters;
        $this->dateTime       = $dateTime;
    }
    
    
    /**
     * @return Uri
     */
    public function uri(): Uri
    {
        return $this->uri;
    }
    
    
    /**
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->getParameters;
    }
    
    
    /**
     * @return DateTime
     */
    public function dateTime(): DateTime
    {
        return $this->dateTime;
    }
}