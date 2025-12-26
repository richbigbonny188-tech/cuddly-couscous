<?php
/*--------------------------------------------------------------------------------------------------
    ContentManagerSaveCommand.php 2021-03-18
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\ContentManager\Command;

use ContentIdentificationInterface;
use ContentNotFoundException;
use ContentReadServiceInterface;
use ContentStatus;
use ContentValueObjectFactory;
use ContentWriteServiceInterface;
use ElementPosition;
use Exception;
use Gambio\StyleEdit\Core\Components\ContentManager\Entities\AbstractContentManagerOption;
use Gambio\StyleEdit\Core\Components\ContentManager\Entities\AbstractContentManagerOptionValue;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\CurrentThemeInterface;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\Language\Services\LanguageService;
use Gambio\StyleEdit\Core\Options\Commands\AbstractSaveCommand;
use InfoElementContent;
use InfoElementContentBuilder;
use UnfinishedBuildException;

/**
 * Class WysiwygSaveCommand
 */
abstract class ContentManagerSaveCommand extends AbstractSaveCommand
{
    /**
     * @var ContentReadServiceInterface
     */
    protected $contentReadService;
    /**
     * @var bool|mixed
     */
    protected $contentValueObjectFactory;
    /**
     * @var ContentWriteServiceInterface
     */
    protected $contentWriteService;
    /**
     * @var CurrentThemeInterface
     */
    protected $currentTheme;
    /**
     * @var string
     */
    protected $defaultTitle;
    /**
     * @var AbstractContentManagerOption
     */
    protected $option;
    /**
     * @var LanguageService
     */
    private $languageService;
    
    
    /**
     * WysiwygSaveCommand constructor.
     *
     * @param ContentWriteServiceInterface $contentWriteService
     * @param ContentReadServiceInterface  $contentReadService
     * @param ContentValueObjectFactory    $contentValueObjectFactory
     * @param LanguageService              $languageService
     * @param CurrentThemeInterface        $currentTheme
     */
    public function __construct(
        ContentWriteServiceInterface $contentWriteService,
        ContentReadServiceInterface $contentReadService,
        ContentValueObjectFactory $contentValueObjectFactory,
        LanguageService $languageService,
        CurrentThemeInterface $currentTheme
    ) {
        $this->contentWriteService       = $contentWriteService;
        $this->contentReadService        = $contentReadService;
        $this->contentValueObjectFactory = $contentValueObjectFactory;
        $this->currentTheme              = $currentTheme;
        $this->languageService           = $languageService;
    }
    
    
    /**
     * Execute the command
     *
     * @throws UnfinishedBuildException
     */
    public function execute(): void
    {
        if (is_a($this->option, AbstractContentManagerOption::class)) {
            $infoElementContent = $this->createInfoElementContent();
            //  is this content zone already saved?
            try {
                $this->contentReadService->findById($this->option->contentIdentification());
                $this->contentWriteService->updateInfoElementContent($infoElementContent);
            } catch (ContentNotFoundException $notFoundException) {
                $this->contentWriteService->storeInfoElementContent($infoElementContent);
            }
        }
    }


    /**
     * @return InfoElementContent
     * @throws UnfinishedBuildException
     * @throws Exception
     */
    protected function createInfoElementContent(): InfoElementContent
    {
        $headingsContent = $textsContent = $titlesContent = [];
    
        /** @var Language $language */
        foreach ($this->languageService->getActiveLanguages() as $language) {
            $title = $this->defaultTitle();
            $value = '';
        
            $valueObject = $this->option->value()[strtolower($language->code())];
        
            /** @var AbstractContentManagerOptionValue $valueObject */
            $headingsContent[] = ['languages_id' => $language->id(), 'content_heading' => ''];

            if($valueObject != NULL) {
                $title = empty($valueObject->title()) ? $this->defaultTitle() : $valueObject->title();
                $value = $valueObject->value();
            }

            $titlesContent[] = ['languages_id' => $language->id(), 'content_title' => $title];
            $textsContent[]  = ['languages_id' => $language->id(), 'content_text' => $value];
        }
    
        $headings = $this->contentValueObjectFactory->createContentHeadingCollection($headingsContent);
        $texts    = $this->contentValueObjectFactory->createContentTextCollection($textsContent);
        $titles   = $this->contentValueObjectFactory->createContentTitleCollection($titlesContent);
        
        if ($this->option->contentIdentification() === null) {
            /**
             * @var ContentIdentificationInterface $identification
             */
            $this->option->setContentIdentification($this->contentReadService->nextContentGroupId());
        }

        if(empty($this->option->contentIdentification()->contentAlias())){
            //create an ID with the currentTheme
            $this->option->setContentIdentification($this->option->contentIdentification()->forTheme($this->currentTheme->id()));
        }
    
        return InfoElementContentBuilder::create()
            ->usingId($this->option->contentIdentification())
            ->usingStatus(new ContentStatus(true))
            ->inPosition(ElementPosition::createForStyleEdit())
            ->usingHeadings($headings)
            ->usingTexts($texts)
            ->usingTitles($titles)
            ->build();
    }
    
    
    /**
     * @return string
     * @throws Exception
     */
    protected function defaultTitle(): string
    {
        if ($this->defaultTitle === null) {
            
            $hash = date('Y-m-d H:i:s') . random_int(0, 100);
            $hash = md5($hash);
            $hash = substr($hash, 0, 6);
            
            $this->defaultTitle = 'StyleEdit-' . $hash;
        }
        
        return $this->defaultTitle;
    }
    
    
    /**
     * Execute the command
     */
    public function rollback(): void
    {
        // TODO: Implement rollback() method.
    }
}