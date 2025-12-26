<?php
/*--------------------------------------------------------------
   MysqlCategoryDescriptionReader.php 2020-09-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Database\Reader;

use Doctrine\DBAL\Connection;
use Gambio\Shop\UserNavigationHistory\Database\DTO\CategoryKeywordDto;

/**
 * Class MysqlCategoryDescriptionReader
 * @package Gambio\Shop\UserNavigationHistory\Database\Reader
 */
class MysqlCategoryDescriptionReader implements CategoryDescriptionReader
{
    /**
     * @var Connection
     */
    protected $connection;
    
    
    /**
     * MysqlCategoryDescriptionReader constructor.
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
    public function categoryIdBySeoKeyword(CategoryKeywordDto $dto): ?int
    {
        $builder = $this->connection->createQueryBuilder();
        $result  = $builder->select('categories_id')
            ->from('categories_description')
            ->where('gm_url_keywords="' . $dto->category() . '"')
            ->andWhere('language_id=' . $dto->languageId())
            ->execute()
            ->fetchAll();
        
        return count($result) ? (int)current($result)['categories_id'] : null;
    }
}