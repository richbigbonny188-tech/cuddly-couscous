<?php
/**
 * CustomizerPresentationId.php 2020-06-10
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);


namespace Gambio\Shop\GxCustomizer\Representation\Entities;

use Gambio\Shop\GXCustomizer\ProductModifiers\Database\ValueObjects\CustomizerModifierIdentifier;
use Gambio\Shop\SellingUnit\Presentation\Entities\AbstractPresentationId;

/**
 * Class AttributePresentationId
 * @package Gambio\Shop\SellingUnit\Presentation\Entities
 */
class CustomizerPresentationId extends AbstractPresentationId
{
    protected const SORT_ORDER = 2000;
    
    /**
     * @var CustomizerModifierIdentifier
     */
    protected $customizerIdentifier;
    
    
    /**
     * CustomizerPresentationId constructor.
     *
     * @param CustomizerModifierIdentifier $customizerIdentifier
     */
    public function __construct(CustomizerModifierIdentifier $customizerIdentifier)
    {
        parent::__construct();
        $this->customizerIdentifier = $customizerIdentifier;
    }
    
    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return '{' . $this->customizerIdentifier->value() . '}0';
    }
}