<?php
/*------------------------------------------------------------------------------
 PropertyModifierBuilder.php 2020-10-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\Properties\ProductModifiers\Database\Builders;

use Gambio\Shop\Properties\ProductModifiers\Database\PropertyModifier;
use Gambio\Shop\ProductModifiers\Modifiers\Builders\AbstractModifierBuilder;
use Gambio\Shop\ProductModifiers\Modifiers\ModifierInterface;

/**
 * Class PropertyModifierBuilder
 * @package Gambio\Shop\Properties\ProductModifiers\Database\Builders
 */
class PropertyModifierBuilder extends AbstractModifierBuilder
{
    
    /**
     * @inheritDoc
     */
    protected function createInstance(): ModifierInterface
    {
        return new PropertyModifier($this->id, $this->info, $this->name, $this->additionalInfo, $this->selected, $this->selectable);
    }


}