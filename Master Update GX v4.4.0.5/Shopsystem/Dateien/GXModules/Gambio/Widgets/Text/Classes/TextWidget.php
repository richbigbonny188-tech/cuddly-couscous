<?php
/*--------------------------------------------------------------------------------------------------
    TextWidget.php 2020-02-03
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\StyleEdit\Core\Components\Wysiwyg\Command\WysiwygSaveCommand;
use Gambio\StyleEdit\Core\Components\Wysiwyg\Entities\WysiwygOption;
use Gambio\StyleEdit\Core\Components\Wysiwyg\Entities\WysiwygOptionValue;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\Options\Entities\FieldSet;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\Widgets\Abstractions\AbstractWidget;
use Gambio\StyleEdit\Core\Widgets\Abstractions\Interfaces\StyleEditApiDataProviderInterface;

/**
 * Class TextWidget
 */
class TextWidget extends AbstractWidget implements StyleEditApiDataProviderInterface
{
    /**
     * @var string
     */
    protected $class;
    /**
     * @var ContentReadServiceInterface
     */
    protected $contentReadService;
    /**
     * @var ContentWriteServiceInterface
     */
    protected $contentWriteService;
    /**
     * @var WysiwygOption
     */
    protected $text;
    
    
    /**
     * TextWidget constructor.
     *
     * @param string                       $static_id
     * @param FieldSet[]                   $fieldsets
     * @param stdClass                     $jsonObject
     * @param ContentReadServiceInterface  $contentReadService
     * @param ContentWriteServiceInterface $contentWriteService
     *
     * @throws Exception
     */
    public function __construct(
        string $static_id,
        array $fieldsets,
        stdClass $jsonObject,
        ContentReadServiceInterface $contentReadService,
        ContentWriteServiceInterface $contentWriteService
    ) {
        parent::__construct($static_id, $fieldsets, $jsonObject);
        $this->static_id           = $static_id;
        $this->jsonObject          = $jsonObject;
        $this->contentWriteService = $contentWriteService;
        $this->contentReadService  = $contentReadService;
    }
    
    
    /**
     * Data can be accessed on route:
     *
     * GXModules/Gambio/StyleEdit/Api/api.php/styleedit/$LANGUAGE_CODE/widget/$THEME_ID/$WIDGET_ID
     *
     * @return JsonSerializable|stdClass|array
     * @throws ContentNotFoundException
     * @throws UnfinishedBuildException
     */
    public static function apiData()
    {
        /** @var ContentReadServiceInterface $contentReadService */
        $contentReadService = StaticGXCoreLoader::getService('ContentRead');
        
        $result               = new stdClass;
        $result->infoElements = $contentReadService->getAllInfoElements();
        
        return $result;
    }
    
    
    /**
     * {@inheritdoc}
     */
    protected static function createWidgetObject(stdClass $jsonObject, $fieldSets = [])
    {
        return new self($jsonObject->id,
                        $fieldSets,
                        $jsonObject,
                        SingletonPrototype::instance()->get(ContentReadServiceInterface::class),
                        SingletonPrototype::instance()->get(ContentWriteServiceInterface::class));
    }
    
    
    /**
     * @param Language|null $currentLanguage
     *
     * @return string
     * @throws Exception
     */
    public function htmlContent(?Language $currentLanguage) : string
    {
        $contentAlias = $this->text->contentIdentification() ? $this->text->contentIdentification()
                                                                          ->contentAlias() : '';
        $widgetClass  = $this->class ? : '';
    
        return "
            <div id=\"{$this->id}\" class=\"{$widgetClass}\">
                {content_manager_alias alias=\"$contentAlias\"}
            </div>";
    }
    
    
    /**
     * @param Language|null $currentLanguage
     *
     * @return string
     * @throws Exception
     */
    public function previewContent(?Language $currentLanguage) : string
    {
        if ($currentLanguage === null) {
            
            throw new Exception('$currentLanguage needs to be set for this widget');
        }
        $content = $this->text->value()[$currentLanguage->code()];
        $wysiwygContent = $content ? $content->value() : '';
        $widgetClass    = $this->class ? : '';
        
        return "
            <div id=\"{$this->id}\" class=\"{$widgetClass}\">
                $wysiwygContent
            </div>";
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() : stdClass
    {
        $result            = $this->jsonObject;
        $result->id        = $this->static_id;
        $result->class     = $this->class;
        $result->type      = 'text';
        $result->fieldsets = $this->fieldsets;
        
        return $result;
    }
    
    
    /**
     * @throws Exception
     */
    public function persist() : void
    {
        if ($this->text->useEditorContent()) {
            
            /** @var WysiwygSaveCommand $command */
            $command = SingletonPrototype::instance()->get(WysiwygSaveCommand::class);
            $command->setOption($this->text);
            
            try {
                $command->execute();
            } catch (Exception $exception) {
                $command->rollback();
                
                throw $exception;
            }
        }
    }
    
    
    public function update() : void
    {
        try {
            $contentManagerEntry = $this->contentReadService->findById($this->text->contentIdentification());
        } catch (ContentNotFoundException $contentNotFoundException) {
            return;
        }
        
        /** @var InfoElementContent $contentManagerEntry */
        foreach ($contentManagerEntry->texts() as $text) {
            /** @var ContentText $text */
            $languageCode = strtolower($text->languageCode());
            if (!isset($this->text->value()[$languageCode])) {
                $this->text->addValue($languageCode, new WysiwygOptionValue('', ''));
            }
            /** @var WysiwygOptionValue $optionValue */
            $optionValue = $this->text->value()[$languageCode];
            $optionValue->setValue($text->content());
        }
        
        foreach ($contentManagerEntry->titles() as $title) {
            /** @var ContentTitle $title */
            $languageCode = strtolower($title->languageCode());
            if (!isset($this->text->value()[$languageCode])) {
                $this->text->addValue($languageCode, new WysiwygOptionValue('', ''));
            }
            /** @var WysiwygOptionValue $optionValue */
            $optionValue = $this->text->value()[$languageCode];
            $optionValue->setTitle($title->content());
        }
    }
    
    
}
