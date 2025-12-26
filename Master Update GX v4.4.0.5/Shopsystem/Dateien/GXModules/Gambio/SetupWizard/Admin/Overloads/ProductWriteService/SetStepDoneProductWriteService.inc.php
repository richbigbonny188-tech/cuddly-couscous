<?php
/* --------------------------------------------------------------
   SetStepDoneProductWriteService.inc.php 2017-01-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class SetStepDoneProductWriteService extends SetStepDoneProductWriteService_parent
{
    /**
     * Create Product
     *
     * Creates a new product and returns the ID of it.
     *
     * @param ProductInterface $product The product to create.
     *
     * @return int The ID of the created product.
     *
     * @throws InvalidArgumentException Through "linkProduct" method.
     */
    public function createProduct(ProductInterface $product)
    {
        MainFactory::create(LegacyCatalogStepDoneCommand::class)->execute();
        
        return parent::createProduct($product);
    }
    
    
    /**
     * Update Product
     *
     * Updates a stored product.
     *
     * @param StoredProductInterface $product The product to update.
     *
     * @return ProductWriteServiceInterface Same instance for chained method calls.
     */
    public function updateProduct(StoredProductInterface $product, stdClass $rawProduct = null)
    {
        MainFactory::create(LegacyCatalogStepDoneCommand::class)->execute();
        
        return parent::updateProduct($product, $rawProduct);
    }
    
}