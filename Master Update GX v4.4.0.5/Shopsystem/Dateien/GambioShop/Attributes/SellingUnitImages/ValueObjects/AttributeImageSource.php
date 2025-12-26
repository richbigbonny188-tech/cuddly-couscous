<?php
/**
 * AttributeImageSource.php 2020-4-6
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitImages\ValueObjects;

use Gambio\Shop\SellingUnit\Images\ValueObjects\AbstractImageSource;

/**
 * Class AttributeImageSource
 *
 * @package Gambio\Shop\SellingUnit\Images\ValueObjects
 */
class AttributeImageSource extends AbstractImageSource
{
    protected const SOURCE = 'attribute';
    protected const SORT_ORDER = 3000;
}