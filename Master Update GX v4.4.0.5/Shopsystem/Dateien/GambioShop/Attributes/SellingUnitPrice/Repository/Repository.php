<?php
/*--------------------------------------------------------------------
 Repository.php 2020-2-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitPrice\Repository;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\Attributes\SellingUnitPrice\Exceptions\NoAttributeOptionValuesIdInModifierCollectionFoundException;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\Dto\OptionIdOptionValuesIdDtoCollection;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\Readers\ReaderInterface;

/**
 * Class Repository
 * @package Gambio\Shop\Attributes\SellingUnitPrice\Repository
 */
class Repository implements RepositoryInterface
{
    /**
     * @var ReaderInterface
     */
    protected $reader;
    
    
    /**
     * Repository constructor.
     *
     * @param ReaderInterface $reader
     */
    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getOptionIdOptionValuesId(
        ModifierIdentifierCollectionInterface $identifierCollection,
        ProductId $productId
    ): OptionIdOptionValuesIdDtoCollection {
        
        return $this->reader->getOptionIdOptionValuesId($identifierCollection, $productId);
    }
}