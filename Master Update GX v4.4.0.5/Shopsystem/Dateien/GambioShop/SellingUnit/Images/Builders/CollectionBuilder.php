<?php
/*--------------------------------------------------------------------------------------------------
    OnCollectionCreateBuilder.php 2020-02-13
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Images\Builders;

use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageInterface;
use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageCollectionInterface;
use Gambio\Shop\SellingUnit\Images\Entities\SellingUnitImageCollection;

/**
 * Class CollectionBuilder
 * @package Gambio\Shop\SellingUnit\Images\Builders
 */
class CollectionBuilder implements CollectionBuilderInterface
{
    
    /**
     * @var array
     */
    protected $images = [];
    
    
    /**
     * @inheritDoc
     */
    public function withImage(SellingUnitImageInterface $image) : CollectionBuilderInterface
    {
        $this->images[] = $image;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function build() : SellingUnitImageCollectionInterface
    {
        $result = new SellingUnitImageCollection($this->images);
        $this->images = [];
        return $result;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withImages(SellingUnitImageCollectionInterface $images) : CollectionBuilderInterface
    {
        foreach($images as $image){
            $this->images[] =  $image;
        }
        return $this;
    }

    public function wipeData(): CollectionBuilderInterface
    {
        $this->images = [];
        return $this;
    }
}