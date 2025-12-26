<?php
/*--------------------------------------------------------------------------------------------------
    CollectionBuilderInterface.php 2020-02-13
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

interface CollectionBuilderInterface
{
    /**
     * @param SellingUnitImageInterface $image
     *
     * @return mixed
     */
    public function withImage(SellingUnitImageInterface $image) : CollectionBuilderInterface;

    /**
     * @return CollectionBuilderInterface
     */
    public function wipeData() : CollectionBuilderInterface;

    
    /**
     * @param SellingUnitImageCollectionInterface $images
     *
     * @return CollectionBuilderInterface
     */
    public function withImages(SellingUnitImageCollectionInterface $images) : CollectionBuilderInterface;
    
    
    /**
     * @return SellingUnitImageCollectionInterface
     */
    public function build() : SellingUnitImageCollectionInterface;
    
}