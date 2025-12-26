<?php
/* --------------------------------------------------------------
 Environment.php 2020-08-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\ValueObjects;

/**
 * Class Environment
 * @package Gambio\Core\Application\ValueObjects
 */
class Environment
{
    /**
     * @var bool
     */
    private $isDev;
    
    
    /**
     * Environment constructor.
     *
     * @param bool $isDev
     */
    public function __construct(bool $isDev)
    {
        $this->isDev = $isDev;
    }
    
    
    /**
     * @return bool
     */
    public function isDev(): bool
    {
        return $this->isDev;
    }
}