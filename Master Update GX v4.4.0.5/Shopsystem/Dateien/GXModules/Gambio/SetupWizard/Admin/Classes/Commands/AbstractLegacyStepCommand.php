<?php
/*------------------------------------------------------------------------------
 AbstractLegacyStepCommand.php 2020-08-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/


abstract class AbstractLegacyStepCommand
{
    const CONFIG_STORAGE_NAMESPACE = 'modules/gambio/setupwizard';
    
    /**
     * @var string
     */
    protected $key;
    /**
     * @var ConfigurationStorage
     */
    protected $storage;
    
    
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->storage = MainFactory::create(ConfigurationStorage::class, static::CONFIG_STORAGE_NAMESPACE);
    }
    
    
    /**
     * @return void
     */
    public abstract function execute() : void;
    
}