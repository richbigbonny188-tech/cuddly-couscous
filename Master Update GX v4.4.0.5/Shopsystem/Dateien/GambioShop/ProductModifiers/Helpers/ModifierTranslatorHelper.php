<?php
/*--------------------------------------------------------------------------------------------------
    ModifierTranslatorHelper.php 2020-12-22
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Helpers;

class ModifierTranslatorHelper
{
    public static function translateGlobals(): void
    {
        $modifierIds = array_merge($_POST['modifiers'] ?? [], $_GET['modifiers'] ?? []);
        if (isset($modifierIds['attribute']) && !(isset($_POST['id']) || isset($_GET['id']))) {
            foreach ($modifierIds['attribute'] as $id => $value) {
                $_POST['id'][$id] = $value;
                $_GET['id'][$id]  = $value;
            }
        }
        if (isset($modifierIds['property'])
            && !(isset($_POST['properties_values_ids'])
                 || isset($_POST['properties_values_ids']))) {
            foreach ($modifierIds['property'] as $id => $value) {
                $_POST['properties_values_ids'][$id] = $value;
                $_GET['properties_values_ids'][$id]  = $value;
            }
        }
        
        self::translateToModifiers();
    }
    
    
    public static function translateGET(): void
    {
        $modifierIds = $_GET['modifiers'] ?? [];
        
        if (isset($modifierIds['attribute']) && !isset($_GET['id'])) {
            foreach ($modifierIds['attribute'] as $id => $value) {
                $_GET['id'][$id] = $value;
            }
        }
        
        if (isset($modifierIds['property']) && !isset($_GET['properties_values_ids'])) {
            foreach ($modifierIds['property'] as $id => $value) {
                $_GET['properties_values_ids'][$id] = $value;
            }
        }
        
        self::translateToModifiers();
    }
    
    
    protected static function translateToModifiers(): void
    {
        if (!(empty($_GET['id']) && empty($_POST['id'])) && !isset($_GET['modifiers'])
            && !isset($_POST['modifiers'])) {
            $_GET['modifiers']  = [];
            $_POST['modifiers'] = [];
        
            if (isset($_POST['id']) && is_iterable($_POST['id'])) {
            
                foreach ($_POST['id'] as $key => $modifierId) {
                
                    if ($modifierId === '0' || $modifierId === 0) {
                        $_GET['modifiers']['customizer'][$key]  = $key;
                        $_POST['modifiers']['customizer'][$key] = $key;
                    } else {
                        $_GET['modifiers']['attribute'][$key]  = $modifierId;
                        $_POST['modifiers']['attribute'][$key] = $modifierId;
                    }
                }
            }
        
            if (isset($_GET['id']) && is_iterable($_GET['id'])) {
            
                foreach ($_GET['id'] as $key => $modifierId) {
                
                    if ($modifierId === '0' || $modifierId === 0) {
                        $_GET['modifiers']['customizer'][$key]  = $key;
                        $_POST['modifiers']['customizer'][$key] = $key;
                    } else {
                        $_GET['modifiers']['attribute'][$key]  = $modifierId;
                        $_POST['modifiers']['attribute'][$key] = $modifierId;
                    }
                }
            }
        }
    }
    
}