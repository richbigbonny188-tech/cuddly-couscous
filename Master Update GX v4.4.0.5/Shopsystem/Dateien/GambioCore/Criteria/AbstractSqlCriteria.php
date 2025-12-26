<?php
/* --------------------------------------------------------------
   AbstractSqlCriteria.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Criteria;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class AbstractSqlCriteria
 *
 * @package Gambio\Admin\Modules\Withdrawal\SqlCriteria
 * @codeCoverageIgnore
 */
abstract class AbstractSqlCriteria implements SqlCriteria
{
    /**
     * @var SqlFilters|SqlFilter[]
     */
    protected $filters = [];
    
    /**
     * @var SqlSortings|SqlSorting[]
     */
    protected $sortings;
    
    
    /**
     * @param QueryBuilder $query
     */
    public function applyToQuery(QueryBuilder $query): void
    {
        foreach ($this->filters as $filter) {
            $value         = $filter->value();
            $column        = $filter->column();
            $columnEscaped = str_replace('.', '_', $column);
            
            if ($filter->operation() === 'like') {
                $value      = str_replace(['%', '*'], ['\%', '%'], $filter->value());
                $filterExpr = $query->expr()->like($column, ':criteria_filter_' . $columnEscaped);
            } elseif ($filter->operation() === 'gt') {
                $filterExpr = $query->expr()->gt($column, ':criteria_filter_' . $columnEscaped);
            } elseif ($filter->operation() === 'gte') {
                $filterExpr = $query->expr()->gte($column, ':criteria_filter_' . $columnEscaped);
            } elseif ($filter->operation() === 'lt') {
                $filterExpr = $query->expr()->lt($column, ':criteria_filter_' . $columnEscaped);
            } elseif ($filter->operation() === 'lte') {
                $filterExpr = $query->expr()->lte($column, ':criteria_filter_' . $columnEscaped);
            } else {
                $filterExpr = $query->expr()->eq($column, ':criteria_filter_' . $columnEscaped);
            }
            
            $query->andWhere($filterExpr)->setParameter(':criteria_filter_' . $columnEscaped, $value);
        }
    
        foreach ($this->sortings as $sorting) {
            $query->addOrderBy($sorting->column(), $sorting->order());
        }
    }
}