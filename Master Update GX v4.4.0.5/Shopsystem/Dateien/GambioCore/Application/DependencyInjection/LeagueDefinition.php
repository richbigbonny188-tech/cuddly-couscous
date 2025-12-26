<?php
/* --------------------------------------------------------------
 LeagueDefinition.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection;

use Gambio\Core\Application\DependencyInjection\Contracts\Definition;
use League\Container\Definition\DefinitionInterface;

/**
 * Class LeagueDefinition
 * @package Gambio\Core\Application\DependencyInjection
 */
class LeagueDefinition implements Contracts\Definition
{
    /**
     * @var DefinitionInterface
     */
    private $internal;
    
    
    /**
     * LeagueDefinition constructor.
     *
     * @param DefinitionInterface $internal
     */
    public function __construct(DefinitionInterface $internal)
    {
        $this->internal = $internal;
    }
    
    
    /**
     * @inheritDoc
     */
    public function addArgument($arg): Definition
    {
        $this->internal->addArgument($arg);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function addArguments(array $args): Definition
    {
        $this->internal->addArguments($args);
        
        return $this;
    }
}