<?php
/* --------------------------------------------------------------
 LegacyShippingStepDoneCommand.php 2020-08-21
 Gambio GmbH
 http://www.gambio.de

 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

/**
 * Class LegacyShippingStepDoneCommand
 */
class LegacyShippingStepDoneCommand extends AbstractLegacyStepDoneCommand
{
    public function __construct()
    {
        parent::__construct('SETUP_WIZARD_STEP_SHIPPING_MODULE');
    }
}