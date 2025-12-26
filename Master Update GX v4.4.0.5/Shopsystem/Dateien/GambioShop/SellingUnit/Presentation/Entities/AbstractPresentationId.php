<?php
/*--------------------------------------------------------------------
 AbstractPresentationId.php 2020-3-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Presentation\Entities;

use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Presentation\ValueObjects\AbstractModifierId;
use RuntimeException;

/**
 * Class AbstractPresentationId
 * @package Gambio\Shop\SellingUnit\Presentation\Entities
 */
abstract class AbstractPresentationId
{
    protected const SORT_ORDER = 0;

    /**
     * @return string
     */
    abstract public function __toString(): string;

    public function __construct()
    {
        if (self::SORT_ORDER === static::SORT_ORDER) {

            throw new RuntimeException(static::class . ' cannot be created unless the constant SORT_ORDER is set');
        }
    }

    /**
     * @return int
     */
    public function sortOrder(): int
    {
        return static::SORT_ORDER;
    }


}