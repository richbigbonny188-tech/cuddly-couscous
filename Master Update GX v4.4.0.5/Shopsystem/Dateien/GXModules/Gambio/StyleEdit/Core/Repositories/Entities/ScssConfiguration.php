<?php
/* --------------------------------------------------------------
  ScssConfiguration.php 2021-02-17
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\StyleEdit\Core\Repositories\Entities;

use Gambio\StyleEdit\Configurations\ShopBasePath;
use Gambio\StyleEdit\Core\SingletonPrototype;

/**
 * Class ScssConfiguration
 * @package Gambio\StyleEdit\Core\Repositories\Entities
 */
class ScssConfiguration extends Configuration
{
    
    /**
     * @var ShopBasePath
     */
    protected $shopBasePath;
    
    
    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function value()
    {
        if (is_string($this->value) && $this->type === 'url' && $this->isRelativePath($this->value)
            && !empty($this->value)) {
            
            return $this->shopBaseUrl()->value() . $this->value;
        }
        
        return parent::value();
    }
    
    
    /**
     * @return ShopBasePath
     * @throws \Exception
     */
    protected function shopBasePath(): ShopBasePath
    {
        if ($this->shopBasePath === null) {
            
            $this->shopBasePath = SingletonPrototype::instance()->get(ShopBasePath::class, dirname(__DIR__, 6));
        }
        
        return $this->shopBasePath;
    }
    
    
    /**
     * @param string $variableValue
     *
     * @description determines if the relative path exists in the filesystem
     *
     * @return bool
     * @throws \Exception
     */
    protected function isRelativePath(string $variableValue): bool
    {
        return file_exists($this->shopBasePath()->value() . DIRECTORY_SEPARATOR . $variableValue);
    }
}