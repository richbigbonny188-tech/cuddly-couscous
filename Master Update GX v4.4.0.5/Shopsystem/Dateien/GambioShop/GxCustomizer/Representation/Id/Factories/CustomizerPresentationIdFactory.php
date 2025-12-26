<?php
/**
 * CustomizerPresentationIdFactory.php 2020-06-10
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);

namespace Gambio\Shop\GxCustomizer\Representation\Id\Factories;

use Gambio\Shop\GxCustomizer\ProductModifiers\Database\ValueObjects\CustomizerModifierIdentifier;
use Gambio\Shop\GxCustomizer\Representation\Entities\CustomizerPresentationId;

/**
 * Class CustomizerPresentationIdFactory
 * @package Gambio\Shop\GxCustomizer\Representation\Id\Factories
 */
class CustomizerPresentationIdFactory
{
    /**
     * @param CustomizerModifierIdentifier $customizerModifierIdentifier
     *
     * @return CustomizerPresentationId
     */
    public function create(CustomizerModifierIdentifier $customizerModifierIdentifier): CustomizerPresentationId
    {
        return new CustomizerPresentationId($customizerModifierIdentifier);
    }
}