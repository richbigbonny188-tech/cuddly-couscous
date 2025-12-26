<?php
/*--------------------------------------------------------------------
 ProductImageSource.php 2020-2-19
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Images\ValueObjects;

/**
 * Class ProductImageSource
 * @package Gambio\Shop\SellingUnit\Images\ValueObjects
 */
class ProductImageSource extends AbstractImageSource
{
    protected const SOURCE = 'product';
    protected const SORT_ORDER = 2000;
}