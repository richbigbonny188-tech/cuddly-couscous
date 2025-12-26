<?php
/*--------------------------------------------------------------------------------------------------
    LongTextBoxOption.php 2020-08-19
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace GXModules\Gambio\StyleEdit\Core\Components\LongTextBox\Entities;

use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentOption;

/**
 * Class LongTextBoxOption
 * @package GXModules\Gambio\StyleEdit\Core\Components\TextArea\Entities
 */
class LongTextBoxOption extends AbstractComponentOption
{
    
    /**
     * @inheritcDoc
     */
    protected function isValid($value): bool
    {
        return true;
    }
    
    
    /**
     * @inheritcDoc
     */
    protected function parseValue($value)
    {
        return $value;
    }
    
    
    /**
     * @inheritcDoc
     */
    public function type(): ?string
    {
        return 'longtextbox';
    }
}