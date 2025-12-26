<?php
/*--------------------------------------------------------------------------------------------------
    AttributeModifierIdentifier.php 2020-12-22
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
declare(strict_types=1);
namespace Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects;

use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\AbstractModifierIdentifier;
use InvalidArgumentException;

/**
 * Class AttributeModifierIdentifier
 * @package Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects
 */
class AttributeModifierIdentifier extends AbstractModifierIdentifier
{
    /**
     * @return int
     */
    public function value() : int
    {
        return (int)parent::value();
    }
    
    
    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function __construct($value)
    {
        $value = ((int)$value);
        
        if ($value === 0) {
            
            throw new InvalidArgumentException(static::class . ' can\'t have the value 0. It\'s a customizer id and not an attribute');
        }
        
        parent::__construct((int)$value);
    }
    
    
    /**
     * @inheritDoc
     */
    public function type() : string
    {
        return 'attribute';
    }
}