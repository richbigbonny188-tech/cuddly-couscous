<?php
/* --------------------------------------------------------------
   DefaultPaymentMethod.php 2020-07-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Configuration\Types;

use Doctrine\DBAL\DBALException;
use Gambio\Core\Configuration\Models\Read\Collections\Options;
use Gambio\Core\Configuration\Repositories\Components\OptionsResolver;

/**
 * Class DefaultPaymentMethod
 *
 * @package Gambio\Core\Configuration\Types
 */
class DefaultPaymentMethod implements ConfigurationType
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
        $connection = $resolver->connection();
        $query      = 'SELECT `value` FROM `gx_configurations` WHERE `key` = "configuration/MODULE_PAYMENT_INSTALLED";';
        try {
            $installedModules = $connection->fetchAssoc($query)['value'];
            $installedModules = str_replace([';;', '.php'], [';', ''], $installedModules);
            $installedModules = explode(";", $installedModules);
            
            // remove hub entry if it exists
            $hubPaymentModuleKey = array_search('gambio_hub', $installedModules, true);
            if ($hubPaymentModuleKey !== false) {
                unset($installedModules[$hubPaymentModuleKey]);
            }
            
            // Add cod if empty
            if (count($installedModules) === 0) {
                $installedModules[] = 'cod';
            }
        } catch (DBALException $e) {
            $installedModules = ['cod'];
        }
        
        $modules = empty($value) ? [['value' => '', 'text' => '']] : [];
        foreach ($installedModules as $installedModule) {
            if (empty($installedModule)) {
                continue;
            }
            $modules[] = [
                'value' => $installedModule,
                'text'  => $resolver->getText('MODULE_PAYMENT_' . strtoupper($installedModule) . '_TEXT_TITLE',
                                              $installedModule),
            ];
        }
        
        return Options::fromArray($modules);
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