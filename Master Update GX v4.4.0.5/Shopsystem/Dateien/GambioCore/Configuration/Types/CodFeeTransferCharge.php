<?php
/* --------------------------------------------------------------
 CodFeeTransferCharge.php 2021-01-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Types;

use Doctrine\DBAL\DBALException;
use Gambio\Core\Configuration\Models\Read\Collections\Options;
use Gambio\Core\Configuration\Repositories\Components\OptionsResolver;
use function array_map;
use function count;
use function explode;
use function in_array;
use function str_replace;

/**
 * Class CodFeeTransferCharge
 * @package Gambio\Core\Configuration\Types
 */
class CodFeeTransferCharge implements ConfigurationType
{
    /**
     * @inheritDoc
     */
    public function toOptions(OptionsResolver $resolver, string $value = null): ?Options
    {
        $shippingModules = $this->installedShippingModules($resolver);
        $fees            = $this->parseFee($resolver, $value, $shippingModules);
        
        return Options::fromArray($fees);
    }
    
    
    /**
     * @inheritDoc
     */
    public function inputType(): string
    {
        return 'cod-fee-input';
    }
    
    
    /**
     * Processes $feeValue to be formatted into an Options compatible format.
     *
     * This function parses the $feeValue into an "text"-, "value"-key array and
     * filters not installed shipping modules from the final data set.
     *
     * @param OptionsResolver $resolver
     * @param string          $feeValue
     * @param array           $shippingModules
     *
     * @return array
     */
    private function parseFee(OptionsResolver $resolver, string $feeValue, array $shippingModules): array
    {
        $options = [];
        $values  = explode('|', $feeValue);
        
        $valuesCount = count($values);
        for ($i = 0; $i < $valuesCount; $i += 2) {
            $key = $values[$i];
            
            if (in_array($key, $shippingModules, true)) {
                $value = $values[$i + 1];
                $name  = $resolver->getText('MODULE_SHIPPING_' . strtoupper($key) . '_TEXT_TITLE',
                                            $key);
                
                $options[] = [
                    'text'    => "{$name} ({$key})",
                    'value'   => $value,
                    'context' => ['key' => $key]
                ];
            }
        }
        
        return $options;
    }
    
    
    /**
     * Fetches installed shipping modules.
     *
     * This function fetches the installed shipping modules and returns
     * the identifiers of them.
     *
     * @param OptionsResolver $resolver
     *
     * @return array
     */
    private function installedShippingModules(OptionsResolver $resolver): array
    {
        $connection = $resolver->connection();
        $query      = 'SELECT `value` FROM `gx_configurations` WHERE `key` = "configuration/MODULE_SHIPPING_INSTALLED";';
        $freequery      = 'SELECT `value` FROM `gx_configurations` WHERE `key` = "configuration/MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING";';
        
        try {
            $shippingModulesString = $connection->fetchAssoc($query)['value'];
            $freeShippingActive = (bool)$connection->fetchAssoc($freequery)['value'];
        } catch (DBALException $e) {
            return [];
        }
        $shippingModules = explode(';', $shippingModulesString);
        if ($freeShippingActive) {
            $shippingModules[] = 'free';
        }
        
        return array_map(static function (string $element): string {
            return str_replace('.php', '', $element);
        },
            $shippingModules);
    }
}