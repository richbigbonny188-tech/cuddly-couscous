<?php
/* --------------------------------------------------------------
   DateTimeFormat.inc.php 2020-11-19
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

/**
 * Class DateTimeFormat
 */
class DateTimeFormat
{
    /**
     * @var int
     */
    protected $dateFormat;
    
    /**
     * @var int
     */
    protected $timeFormat;
    
    
    /**
     * DateTimeFormat constructor.
     *
     * @param int $dateFormat
     * @param int $timeFormat
     */
    public function __construct(int $dateFormat, int $timeFormat)
    {
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
    }
    
    
    /**
     * @return array
     */
    public function toArray(): array
    {
        return [$this->dateFormat, $this->timeFormat];
    }
}