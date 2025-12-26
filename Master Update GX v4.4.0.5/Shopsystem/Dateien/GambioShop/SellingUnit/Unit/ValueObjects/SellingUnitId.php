<?php
/*--------------------------------------------------------------------
 SellingUnitId.php 2020-07-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/
namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;

class SellingUnitId
{
    /**
     * @var LanguageId
     */
    private $language;
    /**
     * @var ModifierIdentifierCollectionInterface
     */
    private $modifiers;
    /**
     * @var ProductId
     */
    private $productId;
    
    
    /**
     * SellingUnitId constructor.
     *
     * @param ModifierIdentifierCollectionInterface $modifiers
     * @param ProductId                             $productId
     * @param LanguageId                            $language
     */
    public function __construct(ModifierIdentifierCollectionInterface $modifiers, ProductId $productId, LanguageId $language)
    {
        $this->modifiers = $modifiers;
        $this->productId = $productId;
        $this->language = $language;
    }
    
    
    
    /**
     * @return LanguageId
     */
    public function language(): LanguageId
    {
        return $this->language;
    }
    
    
    /**
     * @return ModifierIdentifierCollectionInterface
     */
    public function modifiers(): ModifierIdentifierCollectionInterface
    {
        return $this->modifiers;
    }
    
    
    /**
     * @return ProductId
     */
    public function productId(): ProductId
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function hash(): string
    {
        $hast="";
        $hast.="[{$this->productId()->value()}]";
        $hast.="[{$this->language()->value()}]";
        foreach($this->modifiers as $modifier){
            $hast.="[{$modifier->type()}-{$modifier->value()}]";
        }
        return $hast;
    }
    
}