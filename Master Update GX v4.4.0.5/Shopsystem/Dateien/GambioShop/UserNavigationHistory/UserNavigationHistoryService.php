<?php
/*--------------------------------------------------------------
   UserNavigationHistoryService.php 2021-04-06
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory;

use Gambio\Shop\UserNavigationHistory\Collections\UserNavigationHistory;
use Gambio\Shop\UserNavigationHistory\Database\DTO\CategoryKeywordDto;
use Gambio\Shop\UserNavigationHistory\Database\Repository\HistoryRepository;
use Gambio\Shop\UserNavigationHistory\Factories\HistoryFactory;
use InvalidArgumentException;

/**
 * Class UserNavigationHistoryService
 * @package Gambio\Shop\UserNavigationHistory
 */
class UserNavigationHistoryService
{
    /**
     * @var HistoryFactory
     */
    protected $factory;
    
    /**
     * @var UserNavigationHistory
     */
    protected $history;
    
    /**
     * @var HistoryRepository
     */
    protected $repository;
    
    
    /**
     * UserNavigationHistoryService constructor.
     *
     * @param HistoryFactory    $factory
     * @param HistoryRepository $repository
     */
    public function __construct(
        HistoryFactory $factory,
        HistoryRepository $repository
    ) {
        $this->factory    = $factory;
        $this->repository = $repository;
    }
    
    
    /**
     * @param UserNavigationHistory|null $history
     */
    public function setHistory($history): void
    {
        $this->history = $history instanceof UserNavigationHistory ? $history : new UserNavigationHistory;
    }
    
    
    /**
     * @return UserNavigationHistory
     */
    public function history(): UserNavigationHistory
    {
        return $this->history;
    }
    
    
    /**
     * @param string $uri
     * @param array  $getParameters
     */
    public function addHistoryEntry(
        string $uri,
        array $getParameters
    ): void {
        
        if ($this->UriIsAnAssetOrAjaxRequest($uri) === true) {
            
            return;
        }
        
        $newEntry = $this->factory->createHistoryEntry($uri, $getParameters);
        
        $this->history->addEntry($newEntry);
    }
    
    
    /**
     * @param int $languageId
     *
     * @return int|null
     */
    public function getLastBrowsedCategoryId(int $languageId): ?int
    {
        if ($this->history()->count() !== 0) {
            
            foreach ($this->history() as $entry) {
                
                $getParameters = $entry->getParameters();
                
                if (isset($getParameters['cat'])) {
                    
                    return $this->categoryIdFromGetParameter($getParameters['cat']);
                }
                
                if (isset($getParameters['gm_boosted_category'])) {
                    
                    $dto = new CategoryKeywordDto($getParameters['gm_boosted_category'], $languageId);
                    return $this->repository->categoryIdBySeoKeyword($dto);
                }
                
            }
        }
        
        return null;
    }
    
    
    /**
     * @param string $get
     *
     * @return int
     */
    protected function categoryIdFromGetParameter(string $get): int
    {
        if (preg_match('#^c(\d+)_.*html$#', $get, $match)) {
            
            return (int)$match[1];
        }
        
        throw new InvalidArgumentException('Could not determine Category Id from get parameter value: "' . $get . '"');
    }
    
    
    /**
     * @param string $uri
     *
     * @return bool request is a loaded asset or an ajax request
     */
    protected function UriIsAnAssetOrAjaxRequest(string $uri): bool
    {
        return preg_match('#\.(js|png|jpg|jpeg|css|scss|ts)$#', $uri)
               || preg_match('#^/shop.php\?do#', $uri);
    }
}