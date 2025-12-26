<?php
/*--------------------------------------------------------------------
 PropertyImageSource.php 2020-11-27
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\SellingUnitImages\ValueObjects;

use Gambio\Shop\SellingUnit\Images\ValueObjects\AbstractImageSource;

/**
 * Class PropertyImageSource
 *
 * @package Gambio\Shop\SellingUnit\Images\ValueObjects
 * @codeCoverageIgnore no need to test classes without business logic
 */
class PropertyImageSource extends AbstractImageSource
{
    protected const SOURCE = 'property';
    protected const SORT_ORDER = 1000;
}