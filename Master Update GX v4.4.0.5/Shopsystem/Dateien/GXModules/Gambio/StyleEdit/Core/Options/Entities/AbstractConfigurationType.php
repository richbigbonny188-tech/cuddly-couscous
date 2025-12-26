<?php
/*--------------------------------------------------------------------------------------------------
    AbstractConfigurationType.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Options\Entities;

use JsonSerializable;

/**
 * Class AbstractThemeConfiguration
 * @package Gambio\StyleEdit\Core\Components\Entities
 */
abstract class AbstractConfigurationType implements JsonSerializable
{
    /**
     * @var string
     */
    private $basic;
    /**
     * @var bool
     */
    private $hidden;
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    
    private $title;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string|null
     */
    private $pageNamespace;


    /**
     * AbstractConfigurationType constructor.
     *
     * @param string|null $id
     * @param string|null $title
     * @param string|null $type
     * @param string|null $basic
     * @param bool|null $hidden
     * @param string|null $pageNamespace
     */
    public function __construct(
        string $id = null,
        string $title = null,
        string $type = null,
        string $basic = null,
        bool $hidden = null,
        string $pageNamespace = null
    ) {
        $this->id     = $id;
        $this->title  = $title;
        $this->type   = $type;
        $this->basic  = $basic;
        $this->hidden = $hidden;
        $this->pageNamespace = $pageNamespace;
    }
    
    
    /**
     * @return string
     */
    public function basic(): ?string
    {
        return $this->basic;
    }
    
    
    /**
     * @return string
     */
    public function id(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function pageNamespace(): ?string
    {
        return $this->pageNamespace;
    }


    /**
     * @return string
     */
    public function title(): ?string
    {
        return $this->title;
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return $this->type;
    }
    
    
    /**
     * @return bool
     */
    public function hidden(): ?bool
    {
        return $this->hidden;
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    abstract public function jsonSerialize();
    
    
    /**
     * @param $object
     */
    public function initializeFromJsonObject($object): void
    {
        if (isset($object->id)) {
            $this->id = $object->id;
        }
        
        if (isset($object->title)) {
            $this->title = $object->title;
        }
        
        if (isset($object->type)) {
            $this->type = $object->type;
        }
        
        if (isset($object->basic)) {
            $this->basic = $object->basic;
        }
        
        if (isset($object->hidden)) {
            $this->hidden = $object->hidden;
        }

        if (isset($object->pageNamespace)) {
            $this->pageNamespace = $object->pageNamespace;
        }
    }
    
    
}
