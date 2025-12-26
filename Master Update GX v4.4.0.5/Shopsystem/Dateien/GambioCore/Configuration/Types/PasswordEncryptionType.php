<?php
/* --------------------------------------------------------------
   PasswordEncryptionType.php 2020-07-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Configuration\Types;

use Gambio\Core\Configuration\Models\Read\Collections\Options;
use Gambio\Core\Configuration\Repositories\Components\OptionsResolver;

/**
 * Class PasswordEncryptionType
 *
 * @package Gambio\Core\Configuration\Types
 */
class PasswordEncryptionType implements ConfigurationType
{
    /**
     * Resolves possible options for the current type.
     * This is used to provide selectable list in the ui and can be null.
     *
     * @param OptionsResolver $resolver
     * @param string|null     $value
     *
     * @return Options|null
     */
    public function toOptions(OptionsResolver $resolver, string $value = null): ?Options
    {
        $types = [
            [
                'value' => 'password_hash',
                'text'  => $resolver->getText('GM_PASSWORD_ENCRYPTION_TYPE_PASSWORD_HASH', 'gm_security'),
            ],
            [
                'value' => 'md5',
                'text'  => $resolver->getText('GM_PASSWORD_ENCRYPTION_TYPE_MD5', 'gm_security'),
            ],
        ];
        
        return Options::fromArray($types);
    }
    
    
    /**
     * Defines the UI's input type for current configuration type.
     *
     * @return string
     */
    public function inputType(): string
    {
        return 'dropdown';
    }
}