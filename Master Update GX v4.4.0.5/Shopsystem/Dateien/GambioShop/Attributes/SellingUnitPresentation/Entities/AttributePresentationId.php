<?php
/*--------------------------------------------------------------------
 AttributePresentationId.php 2020-3-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitPresentation\Entities;

use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Presentation\Entities\AbstractPresentationId;
use Gambio\Shop\SellingUnit\Presentation\ValueObjects\AbstractModifierId;

/**
 * Class AttributePresentationId
 * @package Gambio\Shop\SellingUnit\Presentation\Entities
 */
class AttributePresentationId extends AbstractPresentationId
{
    protected const SORT_ORDER = 1000;
    /**
     * @var AbstractModifierId
     */
    protected $modifierId;
    
    /**
     * @var ModifierIdentifierInterface
     */
    protected $modifierIdentifier;
    
    
    /**
     * AbstractPresentationId constructor.
     *
     * @param AbstractModifierId          $modifierId
     * @param ModifierIdentifierInterface $modifierIdentifier
     */
    public function __construct(AbstractModifierId $modifierId, ModifierIdentifierInterface $modifierIdentifier)
    {
        parent::__construct();
        $this->modifierId         = $modifierId;
        $this->modifierIdentifier = $modifierIdentifier;
    }
    
    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return '{' . $this->modifierId->value() . '}' . $this->modifierIdentifier->value();
    }
}