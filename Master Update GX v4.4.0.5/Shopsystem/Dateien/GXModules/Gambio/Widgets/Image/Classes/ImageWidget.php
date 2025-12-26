<?php
/*--------------------------------------------------------------------------------------------------
    ImageWidgetProducer.php 2019-10-24
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\StyleEdit\Core\Components\Checkbox\Entities\CheckboxOption;
use Gambio\StyleEdit\Core\Components\DropdownSelect\Entities\DropdownSelectOption;
use Gambio\StyleEdit\Core\Components\NumberBox\Entities\NumberBoxOption;
use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\Options\Entities\FieldSet;
use Gambio\StyleEdit\Core\Widgets\Abstractions\AbstractWidget;
use Gambio\StyleEdit\Core\Widgets\Abstractions\Interfaces\ContentGeneratorInterface;

/**
 * Class ImageWidget
 */
class ImageWidget extends AbstractWidget
{
    protected const FALLBACK_IMAGE = '//www.gambio-shop.de/shop1/images/logos/logo-ihr-shop_logo.png';
    
    /**
     * @var string
     */
    protected $image;
    
    /**
     * @var TextBox
     */
    protected $name;
    
    /**
     * @var TextBox
     */
    protected $class;
    
    /**
     * @var NumberBoxOption
     */
    protected $width;
    
    /**
     * @var NumberBoxOption
     */
    protected $height;
    
    /**
     * @var string
     */
    protected $type = 'image';
    
    /**
     * @var CheckboxOption
     */
    protected $responsive;
    
    /**
     * @var CheckboxOption
     */
    protected $link;
    
    /**
     * @var DropdownSelectOption
     */
    protected $target;
    
    /**
     * @var TextBox
     */
    protected $alt;

    /**
     * @var TextBox
     */
    protected $title;


    /**
     * {@inheritdoc}
     */
    protected static function validateJsonObject(stdClass $jsonObject) : bool
    {
        if (!isset($jsonObject->fieldsets)) {
            throw new InvalidArgumentException('JSON object has no fieldsets');
        }
        
        return parent::validateJsonObject($jsonObject);
    }
    
    
    /**
     * @param Language|null $currentLanguage
     *
     * @return string
     */
    public function htmlContent(?Language $currentLanguage) : string
    {
        $html = $this->htmlTemplate();
        
        $target = $this->target->value($currentLanguage);
        
        $html = str_replace(
            ['{{link begin}}', '{{link end}}'],
            ($link = $this->link->value($currentLanguage)) ? [
                "<a href=\"$link\" target=\"$target\">",
                '</a>'
            ] : [
                '',
                ''
            ],
            $html
        );
        $html = str_replace(
            ['{{link begin}}', '{{link end}}'],
            ($link = $this->link->value($currentLanguage)) ? ['<a href="' . $link . '">', '</a>'] : [
                '',
                ''
            ],
            $html
        );
        
        // searches for all wildcard parameters into the widget template
        preg_match_all('/{{(\w+)}}/', $html, $matches);
        
        if ($matches) {
            [$wildcards, $properties] = $matches;
            
            foreach ($properties as $index => $property) {
                $replaceWith = '';
                if ($this->{$property} && property_exists(self::class, $property)) {
                    $value = $this->{$property}->value($currentLanguage);
                    $replaceWith = $this->parseAttributeValues($property, $value);
                }
                
                $html = str_replace($wildcards[$index], $replaceWith, $html);
            }
        }
        
        // Removing white spaces
        return preg_replace('/\s{2,}/', ' ', $html);
    }
    
    
    /**
     * @return string
     */
    private function htmlTemplate() : string
    {
        return '{{link begin}}' .
               '<img {{image}} {{id}} {{class}} {{width}} {{height}} {{alt}} {{name}} {{title}}/>' .
               '{{link end}}';
    }

    /**
     * @param $property
     * @param $value
     * @return string
     */
    private function parseAttributeValues($property, $value)
    {
        switch ($property) {
            case 'image':
                $attribute = $value ? "src=\"{$value}\"" : '';
                break;
            case 'width':
                // if the image has the responsive option ON we add the attribute width="100%" for bigger resolutions
                $width = $this->responsive->value() ? '100%' : $value;
                $attribute = $width ? "width=\"{$width}\"" : '';
                break;
            case 'height':
                // if the image has the responsive option ON we remove the attribute height=""
                $height = $this->responsive->value() ? '' : $value;
                $attribute = $height ? "height=\"{$height}\"" : '';
                break;
            case 'class':
                $attribute = $this->getClassName($value);
                break;
            default:
                $attribute = $value ? "{$property}=\"{$value}\"" : '';
                break;
        }

        return $attribute;
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $result            = $this->jsonObject;
        $result->type      = 'image';
        $result->id        = $this->static_id;
        $result->fieldsets = $this->fieldsets;
        
        return $result;
    }
    
    
    /**
     * @param $value
     *
     * @return string
     */
    protected function getClassName($value): string
    {
        // if the user has entered, in the class field, "img-responsive" we don't verify
        // the responsive option: to avoid duplicate classes
        if (preg_match('/img\-responsive/', $value)) {
            $classValue = $value;
        } else {
            $responsiveClass = $this->responsive->value() ? 'img-responsive' : '';
            $classValue = $value ? "{$value} {$responsiveClass}" : $responsiveClass;
        }
    
        return $classValue ? "class=\"{$classValue}\"" : '';
    }
}