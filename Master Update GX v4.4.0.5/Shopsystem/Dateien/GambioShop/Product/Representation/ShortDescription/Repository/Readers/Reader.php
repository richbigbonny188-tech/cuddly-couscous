<?php
/* --------------------------------------------------------------
  Reader.php 2021-01-26
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Product\Representation\ShortDescription\Repository\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\Representation\ShortDescription\Exceptions\ShortDescriptionNotFoundException;
use Gambio\Shop\Product\Representation\ShortDescription\Repository\DTO\ShortDescriptionDto;

/**
 * Class Reader
 * @package Gambio\Shop\Product\Representation\ShortDescription\Repository\Readers
 */
class Reader implements ReaderInterface
{
    /**
     * @var Connection
     */
    protected $connection;
    
    
    /**
     * Reader constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function shortDescription(int $productId, LanguageId $languageId): ShortDescriptionDto
    {
        $builder                = $this->connection->createQueryBuilder();
        $shortDescriptionResult = $builder->select('products_short_description')
            ->from('products_description')
            ->where('products_id=' . $productId)
            ->andWhere('language_id=' . $languageId->value())
            ->execute();
        
        if ($shortDescriptionResult->rowCount() === 0) {
        
            throw ShortDescriptionNotFoundException::forProductIdWithTheLanguageId($productId, $languageId);
        }
        
        $shortDescription = (string)$shortDescriptionResult->fetch(FetchMode::ASSOCIATIVE)['products_short_description'];
        
        return new ShortDescriptionDto($shortDescription);
    }
}