<?php
/* --------------------------------------------------------------
 LeagueInflector.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection;

use League\Container\Inflector\InflectorInterface;

/**
 * Class LeagueInflector
 * @package Gambio\Core\Application\DependencyInjection
 */
class LeagueInflector implements Contracts\Inflector
{
    /**
     * @var InflectorInterface
     */
    private $internal;
    
    
    /**
     * LeagueInflector constructor.
     *
     * @param InflectorInterface $internal
     */
    public function __construct(InflectorInterface $internal)
    {
        $this->internal = $internal;
    }
    
    
    /**
     * @inheritDoc
     */
    public function invokeMethod(string $name, array $args): void
    {
        $this->internal = $this->internal->invokeMethod($name, $args);
    }
}