<?php
/*--------------------------------------------------------------------
 SellingUnitIdFactoryInterface.php 2020-2-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Factories\Interfaces;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Interface SellingUnitIdFactoryInterface
 * @package Gambio\Shop\SellingUnit\Unit\Factories\Interfaces
 */
interface SellingUnitIdFactoryInterface
{
    /**
     * @param string     $value
     *
     * @param LanguageId $languageId
     *
     * @return SellingUnitId
     */
    public function createFromProductString(string $value, LanguageId $languageId): SellingUnitId;
    
    
    /**
     * @param string     $value
     *
     * @param LanguageId $languageId
     *
     * @return SellingUnitId
     */
    public function createFromInfoString(string $value, LanguageId $languageId): SellingUnitId;
    
    
    /**
     * @param string|array     $type
     * @param string|array     $value
     * @param LanguageId $languageId
     *
     * @return SellingUnitId
     */
    public function createFromCustom($type, $value, LanguageId $languageId): SellingUnitId;
}