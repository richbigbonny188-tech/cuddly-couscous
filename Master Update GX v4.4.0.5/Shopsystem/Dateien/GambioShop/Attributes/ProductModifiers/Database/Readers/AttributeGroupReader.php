<?php
/*--------------------------------------------------------------------
 AttributeGroupReader.php 2021-01-26
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\Attributes\ProductModifiers\Database\Readers;

use CI_DB_query_builder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOCollection;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOCollectionInterface;
use Gambio\Shop\ProductModifiers\Database\Core\Readers\Interfaces\GroupReaderCompositeInterface;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeGroupIdentifier;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Class AttributeGroupReader
 * @package Gambio\Shop\Attributes\ProductModifiers\Database\Readers
 */
class AttributeGroupReader implements AttributeGroupReaderInterface
{
    /**
     * @var GroupDTOBuilderInterface
     */
    private $builder;
    
    /**
     * @var Connection
     */
    private $connection;
    
    
    /**
     * AttributeGroupReader constructor.
     *
     * @param Connection               $connection
     * @param GroupDTOBuilderInterface $builder
     */
    public function __construct(Connection $connection, GroupDTOBuilderInterface $builder)
    {
        $this->connection = $connection;
        $this->builder    = $builder;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getGroupsByProduct(
        ProductId $id,
        LanguageId $languageId
    ): GroupDTOCollectionInterface {
        $result = new GroupDTOCollection();
        
        // search the database, loop the result and call the builder to return a list
        $sql = "SELECT po.products_options_name, po.products_options_id, po.products_option_display_type
                    FROM products_attributes pa
                        INNER JOIN products_options po ON pa.options_id = po.products_options_id
                    WHERE pa.products_id = {$id->value()} AND po.language_id = {$languageId->value()}
                    GROUP BY po.products_options_name, po.products_options_id, po.products_option_display_type;";
        
        $data = $this->connection->query($sql);
        
        while ($item = $data->fetch(FetchMode::ASSOCIATIVE)) {
            $result->addGroup($this->builder->withId(new AttributeGroupIdentifier((int)$item['products_options_id']))
                ->withName($item['products_options_name'])
                ->withType($item['products_option_display_type'])
                ->withSource('attribute')
                ->build());
        }
        
        return $result;
    }
    
    
    public function getGroupsBySellingUnit(SellingUnitId $id): GroupDTOCollectionInterface
    {
        return $this->getGroupsByProduct($id->productId(), $id->language());
    }
}
