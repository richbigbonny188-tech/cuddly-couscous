<?php
/* --------------------------------------------------------------
 LoadUserPreferencesFromSession.php 2021-02-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Configuration\ConfigurationService;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Application\ValueObjects\Url;
use Gambio\Core\Application\ValueObjects\UserPreferences;
use Gambio\Core\Language\LanguageService;
use Gambio\Core\Language\Exceptions\LanguageNotFoundException;

/**
 * Class LoadUserPreferencesFromSession
 * @package Gambio\Core\Application\Bootstrapper
 * @codeCoverageIgnore
 */
class LoadUserPreferencesFromSession implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        /** @var LanguageService $languageService */
        $languageService = $application->get(LanguageService::class);
        $languageCode    = $this->findLanguageCodeInUrl($application, $languageService);
        
        if (!isset($_SESSION)) {
            if (!empty(substr((string)$_GET['language'], 0, 2))) {
                $newLanguage = substr((string)$_GET['language'], 0, 2) ??
                               ($languageCode ?? $this->getDefaultLanguageCode($application));
            } else {
                $newLanguage = $this->getDefaultLanguageCode($application);
            }
            try {
                $language    = $languageService->getLanguageByCode($newLanguage);
            }
            catch (LanguageNotFoundException $e)
            {
                $newLanguage = $this->getDefaultLanguageCode($application);
                $language    = $languageService->getLanguageByCode($newLanguage);
            }
            $application->registerShared(UserPreferences::class)->addArgument(null)->addArgument($language->id());
            
            return;
        }
        
        if (array_key_exists('language', $_GET) || $languageCode || !array_key_exists('language', $_SESSION)) {
            if (!empty(substr((string)$_GET['language'], 0, 2))) {
                $newLanguage = substr((string)$_GET['language'], 0, 2) ??
                               ($languageCode ?? $this->getDefaultLanguageCode($application));
            } else {
                $newLanguage = $languageCode ?? $this->getDefaultLanguageCode($application);
            }
            try {
                $language    = $languageService->getLanguageByCode($newLanguage);
            }
            catch (LanguageNotFoundException $e)
            {
                $newLanguage = $this->getDefaultLanguageCode($application);
                $language    = $languageService->getLanguageByCode($newLanguage);
            }
            
            $_SESSION['language']         = $language->directory();
            $_SESSION['languages_id']     = $language->id();
            $_SESSION['language_charset'] = $language->charset();
            $_SESSION['language_code']    = $language->code();
        }
        
        $customerId = $this->getSessionValue('customer_id');
        $languageId = $this->getSessionValue('languages_id');
        
        $application->registerShared(UserPreferences::class)->addArgument($customerId)->addArgument($languageId);
    }
    
    
    private function getDefaultLanguageCode(Application $application): string
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = $application->get(ConfigurationService::class);
        $defaultLanguage      = $configurationService->find('configuration/DEFAULT_LANGUAGE');
        
        return ($defaultLanguage !== null) ? $defaultLanguage->value() : 'de';
    }
    
    
    /**
     * Returns the session value of given key, if available and null otherwise.
     *
     * @param string $key
     *
     * @return int|null
     */
    private function getSessionValue(string $key): ?int
    {
        return array_key_exists($key, $_SESSION) ? (int)$_SESSION[$key] : null;
    }


    /**
     * Search for language code in the url, like "en" in http://localhost/shop/en/category/product.html
     * 
     * @param Application     $application
     * @param LanguageService $languageService
     *
     * @return string|null
     */
    private function findLanguageCodeInUrl(Application $application, LanguageService $languageService): ?string
    {
        $languageCode = null;

        /** @var Url $url */
        $url = $application->get(Url::class);
        if ($url->path() === '') {
            $searchPattern = '/^\/(?<code>[a-zA-Z0-9]{2})(\/.*)?$/';
        } else {
            $searchPattern = '/^\/' . str_replace('/', '\/', substr($url->path(), 1))
                             . '\/(?<code>[a-zA-Z0-9]{2})(\/.*)?$/';
        }

        preg_match($searchPattern, $_SERVER['REQUEST_URI'], $matches);

        $availableLanguageCodes = [];
        foreach ($languageService->getAvailableLanguages()->items() as $language) {
            $availableLanguageCodes[] = $language->code();
        }

        if (isset($matches['code']) && in_array($matches['code'], $availableLanguageCodes, true)) {
            $languageCode = $matches['code'];
        }

        return $languageCode;
    }
}