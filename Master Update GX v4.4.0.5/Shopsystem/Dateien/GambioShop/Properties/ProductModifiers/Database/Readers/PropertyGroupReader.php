<?php
/*--------------------------------------------------------------------------------------------------
    PropertyGroupReader.php 2021-01-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
declare(strict_types=1);

namespace Gambio\Shop\Properties\ProductModifiers\Database\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOCollection;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOCollectionInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyGroupIdentifier;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyModifierIdentifier;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Class PropertyGroupReader
 *
 * @package Gambio\Shop\Properties\ProductModifiers\Database\Readers
 * @codeCoverageIgnore
 * @internal currently untestable due to the usage of the CI_DB_query_builder
 */
class PropertyGroupReader implements PropertyGroupReaderInterface
{

    /**
     * @var GroupDTOBuilderInterface
     */
    protected $builder;
    /**
     * @var Connection
     */
    protected $connection;
    
    /**
     * @var array
     */
    protected $previous = [];


    /**
     * PropertyGroupReader constructor.
     *
     * @param Connection $connection
     * @param GroupDTOBuilderInterface $builder
     */
    public function __construct(Connection $connection, GroupDTOBuilderInterface $builder)
    {
        $this->connection = $connection;
        $this->builder    = $builder;
    }
    
    
    /**
     * @param ProductId  $id
     * @param LanguageId $languageId
     *
     * @return GroupDTOCollectionInterface
     * @throws DBALException
     */
    public function getGroupsByProduct(
        ProductId $id,
        LanguageId $languageId
    ): GroupDTOCollectionInterface {
        $result = new GroupDTOCollection();

        $sql = "SELECT p.properties_id, 
                       pd.properties_name, 
                       p.display_type
                    FROM properties p
                        INNER JOIN products_properties_combis ppc on ppc.products_id = {$id->value()}
                        INNER JOIN products_properties_combis_values ppcv on ppc.products_properties_combis_id = ppcv.products_properties_combis_id
                        INNER JOIN properties_values pv on pv.properties_values_id = ppcv.properties_values_id AND pv.properties_id = p.properties_id
                        INNER JOIN properties_description pd on p.properties_id = pd.properties_id AND pd.language_id = {$languageId->value()}
                    GROUP BY p.properties_id, pd.properties_name, p.display_type;";

        $data = $this->connection->query($sql);

        while ($item = $data->fetch(FetchMode::ASSOCIATIVE)) {
            $result->addGroup($this->builder->withId(new PropertyGroupIdentifier((int)$item['properties_id']))
                ->withName($item['properties_name'])
                ->withType($item['display_type'])
                ->withSource('property')
                ->build());
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getGroupsBySellingUnit(
        SellingUnitId $id
    ): GroupDTOCollectionInterface {
        $result             = new GroupDTOCollection();
        $selectedProperties = [-1];
        foreach ($id->modifiers() as $modifier) {
            if ($modifier instanceof PropertyModifierIdentifier) {
                $selectedProperties[] = $modifier->value();
            }
        }
        $selected = implode(',', $selectedProperties);


        $sql            = <<<SQL
                SELECT p.properties_id, 
                       pd.properties_name,
                       max(if(pv.properties_values_id in ($selected), 1, 0) ) selected,
                       p.display_type,
                       p.sort_order,
                       products.properties_dropdown_mode
                    FROM `products`
                        INNER JOIN (
                            SELECT DISTINCT `ppi`.`products_id`, `ppi`.`properties_values_id`, `ppi`.`properties_id`
                            FROM `products_properties_index` `ppi`
                            WHERE `ppi`.products_id = {$id->productId()->value()}
                        ) as `ppi`
                        INNER JOIN `properties_values` `pv` on `pv`.`properties_values_id` = `ppi`.`properties_values_id`
	                    INNER JOIN `properties` `p` ON `p`.`properties_id` = `ppi`.`properties_id`
	                    INNER JOIN `properties_description` `pd` ON `p`.`properties_id` = `pd`.`properties_id` AND `pd`.`language_id` = {$id->language()->value()}
                        WHERE `products`.`products_id` = {$id->productId()->value()}
                    GROUP BY p.properties_id, p.sort_order, pd.properties_name, p.display_type, products.properties_dropdown_mode
                    order by p.sort_order, p.properties_id
SQL;
        $data           = $this->connection->query($sql);
        $this->previous = [];
        $key            = 0;
        while ($item = $data->fetch(FetchMode::ASSOCIATIVE)) {
            $result->addGroup($this->builder->withId(new PropertyGroupIdentifier((int)$item['properties_id']))
                ->withName($item['properties_name'])
                ->withType($item['display_type'])
                ->withSelectable($this->isSelectable($item, $key))
                ->withSource('property')
                ->build());
            $key++;
        }
        return $result;
    }

    /**
     * @param array $item
     * @param int $seq
     *
     * @return bool
     */
    protected function isSelectable(array $item, int $seq): bool
    {
        try {
            if (in_array($item['properties_dropdown_mode'], ['', 'dropdown_mode_1'])) {
                //the two first options show all the groups
                return true;
            } elseif (in_array($item['properties_dropdown_mode'], ['dropdown_mode_2'])) {
                //dropdown_mode_2 show groups only in an specific order
                if ($seq === 0 || (bool)$item['selected']) {
                    return true;
                }

                if (isset($this->previous['selected']) && $this->previous['selected'] === '1') {
                    return true;
                }
            }

            return false;
        } finally {
            $this->previous = $item;
        }
    }

}