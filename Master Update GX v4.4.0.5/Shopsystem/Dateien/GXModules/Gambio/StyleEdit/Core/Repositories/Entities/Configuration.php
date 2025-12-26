<?php
/* --------------------------------------------------------------
   Configuration.php 2021-02-12
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace Gambio\StyleEdit\Core\Repositories\Entities;

use Exception;
use Gambio\StyleEdit\Configurations\ShopBaseUrl;
use Gambio\StyleEdit\Core\SingletonPrototype;
use JsonSerializable;

/**
 * Class Configuration
 * @package Gambio\StyleEdit\Core\Repositories\Entities
 */
class Configuration implements JsonSerializable
{
    /**
     * @var string
     */
    protected const SHOP_BASE_URL_PATTERN = '#^__SHOP_BASE_URL__#';
    
    protected $value;
    protected $id;
    protected $type;
    protected $group;
    
    /**
     * @var ShopBaseUrl
     */
    protected $shopBaseUrl;
    
    
    /**
     * @return mixed
     */
    public function group()
    {
        return $this->group;
    }
    
    
    /**
     * @return mixed
     * @throws Exception
     */
    public function value()
    {
        if (is_string($this->value) && preg_match(self::SHOP_BASE_URL_PATTERN, $this->value) === 1) {
            
            return preg_replace(self::SHOP_BASE_URL_PATTERN, $this->shopBaseUrl()->value(), $this->value);
        }
        
        return $this->value;
    }
    
    
    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }
    
    
    /**
     * @return mixed
     */
    public function type()
    {
        return $this->type;
    }
    
    /**
     * @param $jsonObject
     *
     * @return \Gambio\StyleEdit\Core\Repositories\Entities\Configuration
     * @throws Exception
     */
    public static function createFromJson($jsonObject)
    {
        $result        = SingletonPrototype::instance()->get(static::class);
        $result->value = $jsonObject->value;
        
        if (isset($jsonObject->type)) {
            $result->type = $jsonObject->type;
        }
        
        if (!isset($jsonObject->group) || $jsonObject->group === null) {
            
            throw new Exception('Group unspecified in Configuration with the id :"' . $jsonObject->name . '"');
        }
        
        $result->id    = $jsonObject->name;
        $result->group = $jsonObject->group;
        
        
        return $result;
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     * @throws Exception
     */
    public function jsonSerialize()
    {
        return (object)[
            'name'  => $this->id(),
            'value' => $this->value(),
            'type'  => $this->type(),
            'group' => $this->group()
        
        ];
    }
    
    
    /**
     * @return ShopBaseUrl
     * @throws Exception
     */
    protected function shopBaseUrl(): ShopBaseUrl
    {
        if ($this->shopBaseUrl === null) {
            
            $this->shopBaseUrl = SingletonPrototype::instance()->get(ShopBaseUrl::class, (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);
        }
        
        return $this->shopBaseUrl;
    }
}