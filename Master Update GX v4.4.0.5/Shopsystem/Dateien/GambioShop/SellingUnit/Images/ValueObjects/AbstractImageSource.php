<?php
/*--------------------------------------------------------------------
 AbstractImageSource.php 2020-2-19
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Images\ValueObjects;

use RuntimeException;

/**
 * Class AbstractImageSource
 * Defines the
 * @package Gambio\Shop\SellingUnit\Images\ValueObjects
 */
abstract class AbstractImageSource
{
    /** @var string source of a SellingUnitImage */
    protected const SOURCE = '';
    protected const SORT_ORDER = 0;

    
    /**
     * AbstractImageSource constructor.
     */
    public function __construct()
    {
        if (self::SOURCE === static::SOURCE) {
            
            throw new RuntimeException(static::class . ' cannot be created unless the constant SOURCE is set');
        }
        if (self::SORT_ORDER === static::SORT_ORDER) {

            throw new RuntimeException(static::class . ' cannot be created unless the constant SORT_ORDER is set');
        }
    }
    
    
    /**
     * @return string
     */
    public function value(): string
    {
        return static::SOURCE;
    }

    /**
     * @return int
     */
    public function sortOrder(): int
    {
        return static::SORT_ORDER;
    }
}