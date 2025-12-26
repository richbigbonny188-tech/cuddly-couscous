<?php
/**
 * ProductImageListDeleteAjaxController.inc.php 2020-07-28
 * Last Modified: 2/4/20, 10:41 AM
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);

use Gambio\ProductImageList\Image\Interfaces\ImageInterface;
use Gambio\ProductImageList\Image\ValueObjects\Id;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierId;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierType;
use Gambio\ProductImageList\ImageList\ValueObjects\ListId;
use Gambio\ProductImageList\DeleteService\Exceptions\ListIsUsedForACombinationException;
use Gambio\ProductImageList\DeleteService\Exceptions\ListIsUsedForAnAttributeException;
use Gambio\ProductImageList\Interfaces\ProductImageListDeleteServiceInterface;
use Gambio\ProductImageList\Interfaces\ProductImageListReadServiceInterface;

/**
 * Class ProductImageListDeleteAjaxController
 * Available Endpoints:
 * [DELETE] admin/admin.php?do=ProductImageListDeleteAjax/deleteImageListById&id=<ImageListId>
 * [DELETE] admin/admin.php?do=ProductImageListDeleteAjax/deleteImageById&id=<Comma,Separated,Ids>
 */
class ProductImageListDeleteAjaxController extends AdminHttpViewController
{
    /**
     * @var ProductImageListDeleteServiceInterface
     */
    protected $productImageListDeleteService;
    
    /**
     * @var LanguageTextManager
     */
    protected $languageTextManager;
    
    /**
     * @var ProductImageListReadServiceInterface
     */
    protected $productImageListReadService;
    
    /**
     * @var ProductImageInUseServiceInterface
     */
    protected $imageInUseService;
    
    
    /**
     * Init
     */
    public function init()
    {
        $this->productImageListDeleteService = StaticGXCoreLoader::getService('ProductImageListDelete');
        $this->productImageListReadService   = StaticGXCoreLoader::getService('ProductImageListRead');
        $this->imageInUseService             = StaticGXCoreLoader::getService('ProductImageInUse');
        $this->languageTextManager           = MainFactory::create('LanguageTextManager', 'product_image_lists');
    }
    
    
    public function actionDeleteImageListById() : JsonHttpControllerResponse
    {
        $success = false;
    
        $id           = (int)$this->_getQueryParameter('id');
        $modifierId   = (int)$this->_getQueryParameter('modifierId');
        $modifierType = (string)$this->_getQueryParameter('modifierType');
        try {
            if (!$this->isValidRequestMethod('delete')) {
                throw new Exception(
                    $this->getTranslatedText('CONTROLLER_MESSAGE_INVALID_REQUEST_METHOD')
                );
            }
            if ($id === 0 || $modifierId === 0 || !$modifierType) {
                throw new InvalidArgumentException(
                    $this->getTranslatedText('CONTROLLER_MESSAGE_MISSING_JSON_REQUIRED_INPUT')
                );
            }
            $listId           = new ListId($id);
            $list             = $this->productImageListReadService->getImageListById($id);
            $listModifierId   = new ListModifierId($modifierId);
            $listModifierType = new ListModifierType($modifierType);
            $inUseService     = $this->imageInUseService;
            $images           = array_map(static function (ImageInterface $image) use ($inUseService) {
                $imagePath = $image->localFilePath()->value();
                //  adding images to the array that are currently only used in 1 list or product
                return $inUseService->imageIsInUse(basename($imagePath)) === false ? $imagePath : null;
            },
                $list->toArray());
            $images           = array_filter($images, 'is_string'); //  removing null elements from the array
            $this->productImageListDeleteService->deleteImageList($listId, $listModifierId, $listModifierType);
            array_map('unlink', $images); // deleting the files
            $success = true;
            $message = $this->getTranslatedText('CONTROLLER_MESSAGE_LIST_DELETED');
        } catch (ListIsUsedForACombinationException $e) {
            $message = $this->getTranslatedText('CONTROLLER_MESSAGE_LIST_USED_BY_COMBI');
        } catch (ListIsUsedForAnAttributeException $e) {
            $message = $this->getTranslatedText('CONTROLLER_MESSAGE_LIST_USED_BY_ATTRIBUTE');
        } catch (Throwable $e) {
            $message = $e->getMessage();
        }
    
        $response = [
            'success' => $success,
            'message' => $message,
        ];
    
        return new JsonHttpControllerResponse($response);
    }
    
    
    public function actionDeleteImageById() : JsonHttpControllerResponse
    {
        $success = true;
        try {
            if (!$this->isValidRequestMethod('delete')) {
                throw new Exception(
                    $this->getTranslatedText('CONTROLLER_MESSAGE_INVALID_REQUEST_METHOD')
                );
            }
            $ids = $this->_getIdObjectFromQueryParameters();
            if (!$ids) {
                throw new InvalidArgumentException(
                    $this->getTranslatedText('CONTROLLER_MESSAGE_MISSING_JSON_REQUIRED_INPUT') . ' "Image IDs"'
                );
            }
            $modifierId   = (int)$this->_getQueryParameter('modifierId');
            $modifierType = (string)$this->_getQueryParameter('modifierType');
            if ($modifierId === 0 || !$modifierType) {
                throw new InvalidArgumentException(
                    $this->getTranslatedText('CONTROLLER_MESSAGE_MISSING_JSON_REQUIRED_INPUT') . ' "modifierId/modifierType"'
                );
            }
            $listModifierId   = new ListModifierId($modifierId);
            $listModifierType = new ListModifierType($modifierType);
            $readService  = $this->productImageListReadService;
            $inUseService = $this->imageInUseService;
            $images       = array_map(static function (Id $id) use ($readService, $inUseService) {
                $image     = $readService->getImageById($id->value());
                $imagePath = $image->localFilePath()->value();
            //  adding images to the array that are currently only used in 1 list or product
                return $inUseService->imageIsInUse(basename($imagePath)) === false ? $imagePath : null;
            },
                $ids);
            $images       = array_filter($images, 'is_string'); //  removing null elements from the array
            
            $this->productImageListDeleteService->deleteImage($listModifierId, $listModifierType, ...$ids);
            array_map('unlink', $images); // deleting the files
            
            $message = $this->getTranslatedText('CONTROLLER_MESSAGE_IMAGES_DELETED');
        } catch (Throwable $e) {
            $success = false;
            $message = $e->getMessage();
        }
        $response = [
            'success' => $success,
            'message' => $message,
        ];
        
        return new JsonHttpControllerResponse($response);
    }
    
    
    protected function _getIdObjectFromQueryParameters()
    {
        $param    = $this->_getQueryParameter('id');
        $queryIds = $param ? explode(',', $param) : [];
        $ids      = [];
        foreach ($queryIds as $id) {
            $ids[] = new Id((int)$id);
        }
        
        return $ids;
    }
    
    
    /**
     * @param string $phraseName
     *
     * @return string
     */
    private function getTranslatedText(string $phraseName) : string
    {
        return $this->languageTextManager->get_text($phraseName);
    }
    
}