<?php
/*--------------------------------------------------------------------------------------------------
    GroupInterface.php 2020-08-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Groups;

use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupIdentifierInterface;
use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupName;
use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupStatus;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifiersCollectionInterface;
use Gambio\Shop\ProductModifiers\Presentation\Core\PresentationTypeInterface;

/**
 * Interface GroupInterface
 * @package Gambio\Shop\ProductModifiers\Groups
 */
interface GroupInterface
{
    /**
     * @return string
     */
    public static function source(): string;
    
    
    /**
     * @return GroupIdentifierInterface
     */
    public function id(): GroupIdentifierInterface;
    
    
    /**
     * @return ModifiersCollectionInterface
     */
    public function modifiers(): ModifiersCollectionInterface;
    
    
    /**
     * @return GroupName
     */
    public function name(): GroupName;
    
    /**
     * @return PresentationTypeInterface
     */
    public function type(): PresentationTypeInterface;
    

    /**
     * @return GroupStatus
     */
    public function status() : GroupStatus;

    /**
     * @return bool
     */
    public function isSelected() : bool;
}