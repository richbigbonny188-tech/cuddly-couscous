<?php
/*--------------------------------------------------------------------
 PropertyPresentationId.php 2020-06-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Representation\ValueObjects;

use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Presentation\Entities\AbstractPresentationId;
use Gambio\Shop\SellingUnit\Presentation\ValueObjects\AbstractModifierId;

/**
 * Class PropertyPresentationId
 * @package Gambio\Shop\SellingUnit\Presentation\Entities
 */
class PropertyPresentationId extends AbstractPresentationId
{
    protected const SORT_ORDER = 3000;
    /**
     * @var int
     */
    protected $combisId;
    
    
    /**
     * PropertyPresentationId constructor.
     *
     * @param int $combisId
     */
    public function __construct(int $combisId)
    {
        parent::__construct();
        $this->combisId = $combisId;
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->combisId;
    }
    
    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return 'x' . $this->combisId;
    }
}