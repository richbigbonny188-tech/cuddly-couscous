<?php
/*--------------------------------------------------------------------
 AttributeModifierReader.php 2021-01-26
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\Attributes\ProductModifiers\Database\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeGroupIdentifier;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollection;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Class AttributeModifierReader
 * @package  Gambio\Shop\Attributes\ProductModifiers\Database\Readers
 */
class AttributeModifierReader implements AttributeModifierReaderInterface
{
    /**
     * @var bool
     */
    private $attributeStockCheck;
    /**
     * @var ModifierDTOBuilderInterface
     */
    private $builder;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var bool
     */
    private $hideAttributesOutOfStock;
    /**
     * @var bool
     */
    private $stockAllowCheckout;
    /**
     * @var bool
     */
    private $stockCheck;
    /**
     * @var bool
     */
    private $checkStockBeforeShoppingCart;
    
    
    /**
     * AttributeModifierReader constructor.
     *
     * @param Connection                  $connection
     * @param ModifierDTOBuilderInterface $builder
     * @param bool                        $stockCheck
     * @param bool                        $attributeStockCheck
     * @param bool                        $stockAllowCheckout
     * @param bool                        $setAttributesOutOfStock
     * @param bool                        $checkStockBeforeShoppingCart
     */
    public function __construct(
        Connection $connection,
        ModifierDTOBuilderInterface $builder,
        bool $stockCheck,
        bool $attributeStockCheck,
        bool $stockAllowCheckout,
        bool $setAttributesOutOfStock,
        bool $checkStockBeforeShoppingCart
    ) {
        $this->connection                   = $connection;
        $this->builder                      = $builder;
        $this->stockCheck                   = $stockCheck;
        $this->attributeStockCheck          = $attributeStockCheck;
        $this->stockAllowCheckout           = $stockAllowCheckout;
        $this->hideAttributesOutOfStock     = $setAttributesOutOfStock;
        $this->checkStockBeforeShoppingCart = $checkStockBeforeShoppingCart;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getModifierByProduct(ProductId $id, LanguageId $languageId): ModifierDTOCollectionInterface
    {
        $result = new ModifierDTOCollection();
        
        $sql = "SELECT
                    po.products_options_id,
                    pov.products_options_values_id,
                    pov.products_options_values_name,
                    po.products_option_display_type,
                    pa.options_values_price,
                    pa.price_prefix,
                    pa.attributes_stock,
                    (select max(gm_filename) from products_options_values where products_options_values.`products_options_values_id` = pov.`products_options_values_id`) gm_filename

                    FROM products_attributes pa
                        INNER JOIN products_options po
                                ON po.products_options_id = pa.options_id
                        INNER JOIN products_options_values_to_products_options povtpo
                                ON po.products_options_id = povtpo.products_options_id
                               AND povtpo.products_options_values_id = pa.options_values_id
                        INNER JOIN products_options_values pov
                                ON povtpo.products_options_values_id = pov.products_options_values_id
                               AND pov.language_id = po.language_id
                    WHERE pa.products_id = {$id->value()}
                      AND po.language_id = {$languageId->value()}
                    GROUP BY po.products_options_id, pov.products_options_values_id, pov.products_options_values_name, po.products_option_display_type, pa.sortorder, pa.attributes_stock, pa.options_values_price, pa.price_prefix
                    ORDER BY pa.sortorder";
        
        $query = $this->connection->query($sql);
        while ($item = $query->fetch(FetchMode::ASSOCIATIVE)) {
            $result->addModifier($this->builder->withId(new AttributeModifierIdentifier($item['products_options_values_id']))
                ->withGroupId(new AttributeGroupIdentifier((int)$item['products_options_id']))
                ->withType($item['products_option_display_type'])
                ->withName($item['products_options_values_name'])
                ->withPricePrefix($item['price_prefix'])
                ->withPrice((float)$item['options_values_price'])
                ->withSource('attribute')
                ->withImage($item['gm_filename'] ? 'product_images/attribute_images/' . $item['gm_filename'] : '')
                ->build());
        }
        
        return $result;
    }
    
    
    public function getModifierBySellingUnit(SellingUnitId $id): ModifierDTOCollectionInterface
    {
        $result      = new ModifierDTOCollection();
        $selectedIds = [];
        foreach ($id->modifiers() as $modifier) {
            if ($modifier instanceof AttributeModifierIdentifier) {
                /** @var AttributeModifierIdentifier $modifier */
                $selectedIds [] = $modifier->value();
            }
        }
        
        $sql = "SELECT
                    po.products_options_id,
                    pov.products_options_values_id,
                    pov.products_options_values_name,
                    po.products_option_display_type,
                    pa.options_values_price,
                    pa.price_prefix,
                    pa.attributes_stock,
                    (select max(gm_filename) from products_options_values where products_options_values.`products_options_values_id` = pov.`products_options_values_id`) gm_filename

                    FROM products_attributes pa
                        INNER JOIN products_options po
                                ON po.products_options_id = pa.options_id
                        INNER JOIN products_options_values_to_products_options povtpo
                                ON po.products_options_id = povtpo.products_options_id
                               AND povtpo.products_options_values_id = pa.options_values_id
                        INNER JOIN products_options_values pov
                                ON povtpo.products_options_values_id = pov.products_options_values_id
                               AND pov.language_id = po.language_id
                    WHERE pa.products_id = {$id->productId()->value()}
                      AND po.language_id = {$id->language()->value()}
                    GROUP BY po.products_options_id,
                             pov.products_options_values_id,
                             pov.products_options_values_name,
                             po.products_option_display_type,
                             pa.options_values_price,
                             pa.price_prefix,
                             pa.attributes_stock,
                             pa.sortorder
                    ORDER BY pa.sortorder";
        
        $query   = $this->connection->query($sql);
        $records = $this->setSelectedRecords($selectedIds, $query->fetchAll(0));
        foreach ($records as $item) {
            $attributeId = new AttributeModifierIdentifier($item['products_options_values_id']);
            $result->addModifier($this->builder->withId($attributeId)
                ->withGroupId(new AttributeGroupIdentifier((int)$item['products_options_id']))
                ->withType($item['products_option_display_type'])
                ->withName($item['products_options_values_name'])
                ->withSelectable($this->isSelectable($item))
                ->withSelected($item['selected'])
                ->withPricePrefix($item['price_prefix'])
                ->withPrice((float)$item['options_values_price'])
                ->withSource('attribute')
                ->withImage($item['gm_filename'] ? 'product_images/attribute_images/' . $item['gm_filename'] : '')
                ->build());
        }
        
        return $result;
    }
    
    
    /**
     * @param $item
     *
     * @return bool
     */
    protected function isSelectable($item): ?bool
    {
        //if should hide attributes out of stock and there is no stick return false (!true)
        return !($this->hideAttributesOutOfStock && !$this->hasStock($item));
    }
    
    
    /**
     * @param $item
     *
     * @return bool|null
     */
    protected function hasStock($item): ?bool
    {
        if ($this->stockCheck
            && $this->checkStockBeforeShoppingCart
            && $this->attributeStockCheck
            && !$this->stockAllowCheckout) {
            return ((double)$item['attributes_stock']) > 0;
        } else {
            return true;
        }
    }
    
    
    protected function setSelectedRecords(array $selectedAttributes, array $records)
    {
        $groups = [];
        foreach ($records as &$attribute) {
            $groupId = $attribute['products_options_id'];
            
            if (!isset($groups[$groupId])) {
                $groups[$groupId] = ['selected' => false, 'attributes' => []];
            }
            
            $attribute['selected']   = in_array((int)$attribute['products_options_values_id'], $selectedAttributes);
            $attribute['selectable'] = $this->isSelectable($attribute);
            
            $groups[$groupId]['attributes'][] = &$attribute;
            if ($attribute['selected']) {
                $groups[$groupId]['selected'] = true;
            }
        }
        unset($attribute);
        
        foreach ($groups as &$group) {
            if (!$group['selected']) {
                //try to select the first selectable attribute
                foreach ($group['attributes'] as $key => &$attribute) {
                    if ($attribute['selectable']) {
                        $attribute['selected'] = true;
                        $group['selected']     = true;
                        break;
                    }
                }
                unset($attribute);
                //backup, if there is no selectable attribute then select the first unselectable attribute
                //not testable if no stock the attribute wont be fetched.
                // @codeCoverageIgnoreStart
                if (!$group['selected']) {
                    $group['selected']                  = true;
                    $group['attributes'][0]['selected'] = true;
                }
                // @codeCoverageIgnoreEnd
            }
        }
        unset($group);
        
        foreach ($records as $key => $attribute) {
            if (!$attribute['selectable'] && !$attribute['selected']) {
                unset($records[$key]);
            }
        }
        
        return array_values($records);
    }
}
