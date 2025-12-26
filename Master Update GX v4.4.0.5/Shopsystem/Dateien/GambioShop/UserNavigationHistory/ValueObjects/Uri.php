<?php
/*--------------------------------------------------------------
   Uri.php 2020-09-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\ValueObjects;

/**
 * Class Uri
 * @package Gambio\Shop\UserNavigationHistory\ValueObjects
 */
class Uri
{
    /**
     * @var string
     */
    protected $uri;
    
    
    /**
     * Url constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }
    
    
    /**
     * @return string
     */
    public function value(): string
    {
        return $this->uri;
    }
}