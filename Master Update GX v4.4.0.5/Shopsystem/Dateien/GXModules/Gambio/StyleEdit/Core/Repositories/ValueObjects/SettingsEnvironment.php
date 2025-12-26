<?php
/* --------------------------------------------------------------
  SettingsEnvironment.php 2021-02-17
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\StyleEdit\Core\Repositories\ValueObjects;

/**
 * Class SettingsEnvironment
 * @package Gambio\StyleEdit\Core\Repositories\ValueObjects
 */
class SettingsEnvironment
{
    /**
     * @var string
     */
    private $environment;
    
    
    /**
     * SettingsEnvironment constructor.
     *
     * @param string $environment
     */
    public function __construct(string $environment = 'styleedit')
    {
        $this->environment = $environment;
    }
    
    
    /**
     * @return string
     */
    public function value(): string
    {
        return $this->environment;
    }
}