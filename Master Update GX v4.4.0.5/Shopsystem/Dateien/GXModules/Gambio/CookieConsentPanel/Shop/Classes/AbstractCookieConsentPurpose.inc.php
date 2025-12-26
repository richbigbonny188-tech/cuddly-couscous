<?php
/*--------------------------------------------------------------------------------------------------
    AbstractCookieConsentPurpose.php 2020-01-10
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class AbstractCookieConsentPurpose
 */
abstract class AbstractCookieConsentPurpose implements CookieConsentPurposeInterface
{
    
    /**
     * @inheritDoc
     */
    abstract public function purposeCode(): int;
    
    
    /**
     * @inheritDoc
     */
    abstract public function description(): int;
    
    
    /**
     * @inheritDoc
     */
    abstract public function name(): int;
}