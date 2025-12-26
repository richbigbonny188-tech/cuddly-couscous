<?php
/*------------------------------------------------------------------------------
 PropertyModifierReaderCache.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\ProductModifiers\Database\Readers;

use Doctrine\DBAL\DBALException;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyModifierIdentifier;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

class CachedPropertyModifierReader implements PropertyModifierReaderInterface
{
    /**
     * @var PropertyModifierReader
     */
    protected $reader;
    
    
    /**
     * CachedPropertyModifierReader constructor.
     *
     * @param PropertyModifierReader $reader
     */
    public function __construct(PropertyModifierReader $reader)
    {
        $this->reader = $reader;
    }
    
    protected $modifierBySellingUnit = [];
    
    
    /**
     * @inheritDoc
     * @throws DBALException
     */
    public function getModifierBySellingUnit(SellingUnitId $id): ModifierDTOCollectionInterface
    {
        $modifiers = [];
        foreach ($id->modifiers() as $modifier) {
            if ($modifier instanceof PropertyModifierIdentifier) {
                $modifiers[] = $modifier;
            }
        }
        $newId = new SellingUnitId(new ModifierIdentifierCollection($modifiers), $id->productId(), $id->language());
        if (!isset($this->modifierBySellingUnit[$newId->hash()])) {
            $this->modifierBySellingUnit[$newId->hash()] = $this->reader->getModifierBySellingUnit($id);
        }
        return $this->modifierBySellingUnit[$newId->hash()];
    }
    
    
}