<?php
/* --------------------------------------------------------------
   ProductWriteService.inc.php 2020-09-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class ProductWriteService
 *
 * @category   System
 * @package    Product
 */
class ProductWriteService implements ProductWriteServiceInterface
{
    /**
     * The product repository.
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepo;
    
    /**
     * The product image storage.
     *
     * @var AbstractFileStorage
     */
    protected $productImageStorage;
    
    /**
     * The product category linker.
     *
     * @var ProductCategoryLinkerInterface
     */
    protected $productLinker;
    
    /**
     * EnvProductImagePathsSettings.
     *
     * @var ProductImagePathsSettingsInterface
     */
    protected $envProductImagePathsSettings;
    
    /**
     * Used for fetching the language data.
     *
     * @var LanguageProviderInterface
     */
    protected $languageProvider;
    
    /**
     * Used for writing and repairing product's url keywords
     *
     * @var UrlKeywordsRepairerInterface
     */
    protected $urlKeywordsRepairer;
    
    /**
     * @var \DeleteHistoryWriteService
     */
    protected $deleteHistoryService;
    
    
    /**
     * ProductWriteService constructor.
     *
     * @param \ProductRepositoryInterface         $productRepo
     * @param \AbstractFileStorage                $productImageStorage
     * @param \ProductCategoryLinkerInterface     $productLinker
     * @param \ProductImagePathsSettingsInterface $envProductImagePathsSettings
     * @param \LanguageProviderInterface          $languageProvider
     * @param \UrlKeywordsRepairerInterface       $urlKeywordsRepairer
     * @param \DeleteHistoryWriteService          $deleteHistoryService
     */
    public function __construct(
        ProductRepositoryInterface $productRepo,
        AbstractFileStorage $productImageStorage,
        ProductCategoryLinkerInterface $productLinker,
        ProductImagePathsSettingsInterface $envProductImagePathsSettings,
        LanguageProviderInterface $languageProvider,
        UrlKeywordsRepairerInterface $urlKeywordsRepairer,
        DeleteHistoryWriteService $deleteHistoryService
    ) {
        $this->productRepo                  = $productRepo;
        $this->productImageStorage          = $productImageStorage;
        $this->productLinker                = $productLinker;
        $this->envProductImagePathsSettings = $envProductImagePathsSettings;
        $this->languageProvider             = $languageProvider;
        $this->urlKeywordsRepairer          = $urlKeywordsRepairer;
        $this->deleteHistoryService         = $deleteHistoryService;
    }
    
    
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
        $productId = $this->productRepo->add($product);
        
        $this->linkProduct(new IdType($productId), new IdType(0)); // 0 is the default category value.
        
        // set url keywords
        $this->urlKeywordsRepairer->repair('products', $productId);
        
        return $productId;
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
        $this->productRepo->store($product);
    
        if (isset($rawProduct->quantity) && !isset($rawProduct->shippingTimeId)) {
            require_once(DIR_FS_CATALOG.'gm/inc/set_shipping_status.php');
            set_shipping_status($product->getProductId(),NULL,$product->getQuantity());
        }
        
        // set url keywords
        $this->urlKeywordsRepairer->repair('products', $product->getProductId());
        
        return $this;
    }
    
    
    /**
     * Delete Product
     *
     * Deletes a specific product, depending on the provided product ID.
     *
     * @param IdType $productId The product ID of the product to delete.
     *
     * @return ProductWriteServiceInterface Same instance for chained method calls.
     */
    public function deleteProductById(IdType $productId)
    {
        $this->productLinker->deleteProductLinks($productId);
        
        $product = $this->productRepo->getProductById($productId);
        $this->productImageStorage->deleteFile(new RelativeFilePathStringType($product->getPrimaryImage()->getFilename()));
        foreach ($product->getAdditionalImages()->getArray() as $additionalImage) {
            $this->productImageStorage->deleteFile(new RelativeFilePathStringType($additionalImage->getFilename()));
        }
        
        $this->productRepo->deleteProductById($productId);
        
        $id    = DeletedId::create((string)$productId->asInt());
        $scope = DeleteHistoryScope::products();
        $this->deleteHistoryService->reportDeletion($id, $scope);
        
        return $this;
    }
    
    
    /**
     * Duplicate Product
     *
     * Duplicates a product to a category.
     *
     * @param IdType   $productId             The product ID of the product to duplicate.
     * @param IdType   $targetCategoryId      The target category ID of the product to be duplicated to.s
     * @param BoolType $duplicateAttributes   Should the attributes be duplicated also?
     * @param BoolType $duplicateSpecials     Should the specials be duplicated also?
     * @param BoolType $duplicateCrossSelling Should cross selling be duplicated also?
     *
     * @return int Returns the ID of the new product.
     *
     * @throws InvalidArgumentException If "$newProductId" is not an integer.
     *
     * @todo Implement the last three arguments when finished in UML.
     */
    public function duplicateProduct(
        IdType $productId,
        IdType $targetCategoryId,
        BoolType $duplicateAttributes = null,
        BoolType $duplicateSpecials = null,
        BoolType $duplicateCrossSelling = null
    ) {
        $storedProduct = $this->productRepo->getProductById($productId);
        $newProductId  = new IdType($this->productRepo->add($storedProduct));
        $this->linkProduct($newProductId, $targetCategoryId);
        
        // copy product images
        $storedProductImageContainer = $storedProduct->getImageContainer();
        $duplicatedProduct           = $this->productRepo->getProductById($newProductId);
        $duplicatedImageContainer    = MainFactory::create('ProductImageContainer');
        
        // primary image
        $duplicatedPrimaryImage = $this->duplicateProductImage($storedProductImageContainer->getPrimary());
        $duplicatedImageContainer->setPrimary($duplicatedPrimaryImage);
        
        // additional images
        foreach ($storedProductImageContainer->getAdditionals()->getArray() as $additionalImage) {
            $duplicatedAdditionalImage = $this->duplicateProductImage($additionalImage);
            $duplicatedImageContainer->addAdditional($duplicatedAdditionalImage);
        }
        
        $duplicatedProduct->setImageContainer($duplicatedImageContainer);
        $this->productRepo->store($duplicatedProduct);
        
        // set url keywords
        $this->urlKeywordsRepairer->repair('products', $newProductId->asInt());
        
        return $newProductId->asInt();
    }
    
    
    /**
     * Link Product
     *
     * Links a product to a category.
     *
     * @param IdType $productId        The product ID of the product to link.
     * @param IdType $targetCategoryId The target category ID, of the category to be linked to.
     *
     * @return ProductWriteService Same instance for chained method calls.
     */
    public function linkProduct(IdType $productId, IdType $targetCategoryId)
    {
        $this->productLinker->linkProduct($productId, $targetCategoryId);
        
        return $this;
    }
    
    
    /**
     * Unlink a product.
     * Deletes the product if all category links are removed.
     *
     * @param \IdType      $productId   Id of product to be unlinked.
     * @param IdCollection $categoryIds Ids of categories to be unlinked.
     *
     * @return ProductWriteServiceInterface|$this Same instance for chained method calls.
     */
    public function unlinkProduct(IdType $productId, IdCollection $categoryIds)
    {
        $links  = $this->productLinker->getProductLinks($productId)->getIntArray();
        $catIds = $categoryIds->getIntArray();
        sort($links);
        sort($catIds);
        
        if ($links === $catIds) {
            $this->productLinker->deleteProductLinks($productId);
            $this->deleteProductById($productId);
            
            return $this;
        }
        
        if (in_array(0, $catIds)) {
            $this->productLinker->removeProductFromStartPage($productId);
        }
        
        foreach ($catIds as $catId) {
            $this->deleteProductLink($productId, new IdType($catId));
        }
        
        return $this;
    }
    
    
    /**
     * Changes the category link of a product.
     *
     * @param IdType $productId         The product ID of the product to move.
     * @param IdType $currentCategoryId Old category ID of the product.
     * @param IdType $newCategoryId     New category ID of the product.
     *
     * @return ProductWriteService Same instance for chained method calls.
     */
    public function changeProductLink(IdType $productId, IdType $currentCategoryId, IdType $newCategoryId)
    {
        $this->productLinker->changeProductLink($productId, $currentCategoryId, $newCategoryId);
        
        return $this;
    }
    
    
    /**
     * Removes a category link from a product by the given product id.
     *
     * @param IdType $productId  Id of the product.
     * @param IdType $categoryId Id of category from where the product is link is to delete.
     *
     * @return ProductWriteService Same instance for chained method calls.
     */
    public function deleteProductLink(IdType $productId, IdType $categoryId)
    {
        $this->productLinker->deleteProductLink($productId, $categoryId);
        
        return $this;
    }
    
    
    /**
     * Import Product Image File
     *
     * Imports an image for the product.
     *
     * @param ExistingFile       $sourceFile        The existing file to import.
     * @param FilenameStringType $preferredFilename The preferred filename.
     *
     * @return string The new filename.
     * @throws InvalidArgumentException If the provided source file or the preferred filename is not valid.
     *
     */
    public function importProductImageFile(ExistingFile $sourceFile, FilenameStringType $preferredFilename)
    {
        return $this->productImageStorage->importFile($sourceFile, $preferredFilename);
    }
    
    
    /**
     * Rename Product Image File
     *
     * Renames a product image file.
     *
     * @param FilenameStringType $oldName The old name of the product image file.
     * @param FilenameStringType $newName The new name of the product image file.
     *
     * @return ProductWriteService Same instance for chained method calls.
     * @throws InvalidArgumentException If the provided old name or new name is not valid.
     *
     */
    public function renameProductImage(FilenameStringType $oldName, FilenameStringType $newName)
    {
        $this->productImageStorage->renameFile($oldName, $newName);
        
        return $this;
    }
    
    
    /**
     * Delete Product Image
     *
     * Deletes a product image.
     *
     * @param FilenameStringType $filename The filename of the product image to delete.
     *
     * @return ProductWriteService Same instance for chained method calls.
     */
    public function deleteProductImage(FilenameStringType $filename)
    {
        $this->productImageStorage->deleteFile($filename);
        
        return $this;
    }
    
    
    /**
     * Processes an image for the front end.
     *
     * @param FilenameStringType $productImage
     *
     * @return ProductWriteService Same instance for chained method calls.
     */
    public function processProductImage(FilenameStringType $productImage)
    {
        $this->productImageStorage->processImage($productImage);
        
        return $this;
    }
    
    
    /**
     * Removes all category links from a product by given product ID.
     *
     * @param IdType $productId ID of product.
     *
     * @return ProductWriteService Same instance for chained method calls.
     */
    public function deleteProductLinks(IdType $productId)
    {
        $this->productLinker->deleteProductLinks($productId);
        
        return $this;
    }
    
    
    /**
     * Duplicates a given Product Image and set the properties accordingly to the provided Source Product Image
     *
     * @param ProductImageInterface $sourceProductImage The Product Image to duplicate.
     *
     * @return ProductImageInterface The duplicated Product Image.
     */
    protected function duplicateProductImage(ProductImageInterface $sourceProductImage)
    {
        $originalImagesDirectoryPath = $this->envProductImagePathsSettings->getProductOriginalImagesDirPath();
        $filename                    = new FilenameStringType($sourceProductImage->getFilename());
        $filepath                    = new ExistingFile(new NonEmptyStringType($originalImagesDirectoryPath
                                                                               . $sourceProductImage->getFilename()));
        
        $duplicatedImageFileName = $this->importProductImageFile($filepath, $filename);
        $duplicatedImage         = MainFactory::create('ProductImage',
                                                       new FilenameStringType($duplicatedImageFileName));
        
        $duplicatedImage->setVisible(new BoolType($sourceProductImage->isVisible()));
        foreach ($this->languageProvider->getCodes() as $languageCode) {
            $duplicatedImage->setAltText(new StringType($sourceProductImage->getAltText($languageCode)), $languageCode);
        }
        
        return $duplicatedImage;
    }
}
