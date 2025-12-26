<?php
/*--------------------------------------------------------------------
 OptionIdOptionValuesIdDto.php 2020-2-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitPrice\Repository\Dto;

/**
 * Class OptionIdOptionValuesIdDto
 * @package Gambio\Shop\Attributes\SellingUnitPrice\Repository\Dto
 */
class OptionIdOptionValuesIdDto
{
    /**
     * @var int
     */
    protected $optionId;
    
    /**
     * @var int
     */
    protected $optionValuesId;
    
    
    /**
     * OptionIdOptionValuesIdDto constructor.
     *
     * @param int $optionId
     * @param int $optionValuesId
     */
    public function __construct(int $optionId, int $optionValuesId)
    {
        $this->optionId = $optionId;
        $this->optionValuesId = $optionValuesId;
    }
    
    
    /**
     * @return int
     */
    public function optionId(): int
    {
        return $this->optionId;
    }
    
    
    /**
     * @return int
     */
    public function optionValuesId(): int
    {
        return $this->optionValuesId;
    }
}