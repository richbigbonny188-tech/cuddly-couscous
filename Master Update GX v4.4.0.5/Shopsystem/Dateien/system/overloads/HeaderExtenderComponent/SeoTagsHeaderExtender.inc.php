<?php
/* --------------------------------------------------------------
	SeoTagsHeaderExtender.inc.php 2021-02-17
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2021 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

/**
 * Class SeoTagsHeaderExtender
 *
 * Determines output of canonical links, alternate links and prev/next links.
 *
 * Please also refer to SeoTagsDataProvider.
 *
 */
class SeoTagsHeaderExtender extends SeoTagsHeaderExtender_parent
{
	/** @var \GMSEOBoost_ORIGIN */
	protected $seoBoost;
	
	/** @var \LanguageProviderInterface */
	protected $languageProvider;
	
	/** @var \KeyValueCollection */
	protected $activeCodes;
	
	/** @var \LanguageCode */
	protected $languageCode;
	
	/** @var \LanguageCode */
	protected $defaultLangCode;
	
	/** @var bool */
	protected $indexedPage = true;
	
	/** @var bool */
	protected $followLinks = true;
	
	/** @var array */
	protected $excludeGetParams;
	
	/** @var array */
	protected $noIndexKeys;
	
	/** @var array */
	protected $noIndexKeysPerPageType;
	
	/** @var array */
	protected $noFollowKeysPerPageType;
	
	/** @var array */
	protected $noRelPrevNext;
	
	/** @var array */
	protected $noCanonicalFiles;
	
	/** @var array */
	protected $excludeKeysFromCanonical;
	
	/** @var array */
	protected $excludeKeysFromCanonicalPerPageType;
	
	public function proceed()
	{
		parent::proceed();
		
		$this->excludeGetParams                    = SeoTagsDataProvider::getExcludeGetParams();
		$this->noIndexKeys                         = SeoTagsDataProvider::getNoIndexKeys();
		$this->noIndexKeysPerPageType              = SeoTagsDataProvider::getNoIndexKeysPerPageType();
		$this->noFollowKeysPerPageType             = SeoTagsDataProvider::getNoFollowKeysPerPageType();
		$this->noRelPrevNext                       = SeoTagsDataProvider::getNoRelPrevNext();
		$this->noCanonicalFiles                    = SeoTagsDataProvider::getNoCanonicalFiles();
		$this->excludeKeysFromCanonical            = SeoTagsDataProvider::getExcludeKeysFromCanonical();
		$this->excludeKeysFromCanonicalPerPageType = SeoTagsDataProvider::getExcludeKeysFromCanonicalPerPageType();
		
		$db = StaticGXCoreLoader::getDatabaseQueryBuilder();
		/** @var \GMSEOBoost_ORIGIN $seoBoost */
		$this->seoBoost         = MainFactory::create_object('GMSEOBoost', [], true);
		$this->languageCode     = new LanguageCode(new StringType(strtoupper($_SESSION['language_code'])));
		$this->languageProvider = MainFactory::create('LanguageProvider',
		                                              StaticGXCoreLoader::getDatabaseQueryBuilder());
		$this->activeCodes      = $this->languageProvider->getActiveCodes();
		$this->defaultLangCode  = $this->languageProvider->getDefaultLanguageCode();
		
		$allowedGetKeys = array_diff(array_keys($_GET), $this->excludeGetParams);
		
		$this->indexedPage = count(array_intersect($this->noIndexKeys, $allowedGetKeys)) === 0;
        
        $isProductPage          = isset($GLOBALS['product']) && $GLOBALS['product']->isProduct === true;
        $isCategoryPage         = isset($_GET['cat'], $GLOBALS['current_category_id'])
                                  && !empty($GLOBALS['current_category_id']);
        $isContentPage          = (!empty($_GET['coID'])
                                   && strpos($GLOBALS['PHP_SELF'], '/shop_content.php') !== false);
        $isIndexPage            = stripos(gm_get_env_info('PHP_SELF'), 'index.php') !== false;
        $useBoostedLanguageCode = gm_get_conf('USE_SEO_BOOST_LANGUAGE_CODE') === 'true';
		
		$devMode = file_exists(DIR_FS_CATALOG . '/.dev-environment');
		if($devMode)
		{
			header('X-SeoTags-Debug: ' . sprintf('product %s cat %s content %s index %s boosted %s',
			                                     var_export($isProductPage, true), var_export($isCategoryPage, true),
			                                     var_export($isContentPage, true), var_export($isIndexPage, true),
			                                     var_export($useBoostedLanguageCode, true)));
			header('X-SeoTags-GetArray: ' . implode(',', $allowedGetKeys));
			header('X-SeoTags-Get: ' . http_build_query($_GET));
		}
		if($isProductPage)
		{
			$pageHtml = $this->getProductPageHtml();
			$this->indexedPage = $this->indexedPage &&
			                     count(array_intersect(array_keys($_GET), $this->noIndexKeysPerPageType['product'])) === 0;
			$this->followLinks = $this->followLinks &&
			                     count(array_intersect(array_keys($_GET), $this->noFollowKeysPerPageType['product'])) === 0;
		}
		elseif($isCategoryPage)
		{
			$pageHtml = $this->getCategoryPageHtml();
			$this->indexedPage = $this->indexedPage &&
			                     count(array_intersect(array_keys($_GET), $this->noIndexKeysPerPageType['category'])) === 0;
			$this->followLinks = $this->followLinks &&
			                     count(array_intersect(array_keys($_GET), $this->noFollowKeysPerPageType['category'])) === 0;
		}
		elseif($isContentPage)
		{
			$pageHtml = $this->getContentPageHtml();
			$robotsRow = $db->select('gm_robots_entry')
			                ->get_where('content_manager',
			                            ['content_group' => (int)$_GET['coID'],
			                             'languages_id' => (int)$_SESSION['languages_id']]
			                )->row_array();
			if((int)$robotsRow['gm_robots_entry'] === 1)
			{
				$this->indexedPage = false;
			}
			$this->indexedPage = $this->indexedPage &&
			                     count(array_intersect(array_keys($_GET), $this->noIndexKeysPerPageType['content'])) === 0;
			$this->followLinks = $this->followLinks &&
			                     count(array_intersect(array_keys($_GET), $this->noFollowKeysPerPageType['content'])) === 0;
		}
		elseif($isIndexPage)
		{
			$pageHtml = $this->getIndexPageHtml();
			$this->indexedPage = $this->indexedPage &&
			                     count(array_intersect(array_keys($_GET), $this->noIndexKeysPerPageType['index'])) === 0;
			$this->followLinks = $this->followLinks &&
			                     count(array_intersect(array_keys($_GET), $this->noFollowKeysPerPageType['index'])) === 0;
		}
		elseif($useBoostedLanguageCode)
		{
			$pageHtml = $this->getBoostedLanguageCodeHtml();
			$this->indexedPage = $this->indexedPage &&
			                     count(array_intersect(array_keys($_GET), $this->noIndexKeysPerPageType['boosted'])) === 0;
			$this->followLinks = $this->followLinks &&
			                     count(array_intersect(array_keys($_GET), $this->noFollowKeysPerPageType['boosted'])) === 0;
		}
		else
		{
			$pageHtml = $this->getOtherHtml();
			$this->indexedPage = $this->indexedPage &&
			                     count(array_intersect(array_keys($_GET), $this->noIndexKeysPerPageType['other'])) === 0;
			$this->followLinks = $this->followLinks &&
			                     count(array_intersect(array_keys($_GET), $this->noFollowKeysPerPageType['other'])) === 0;
		}
		
		if($this->indexedPage === true)
		{
			$noIndexFiles = SeoTagsDataProvider::getNoIndexFiles();
			foreach($noIndexFiles as $noIndexFile)
			{
				if(strpos($_SERVER['REQUEST_URI'], $noIndexFile) !== false)
				{
					$this->indexedPage = false;
					break;
				}
			}
		}
		
		$robotsIndex  = $this->indexedPage ? 'index' : 'noindex';
		$robotsFollow = $this->followLinks ? 'follow' : 'nofollow';
		$html         = sprintf('<meta name="robots" content="%s,%s" />' . "\n\t\t", $robotsIndex, $robotsFollow);
		if(headers_sent() === false)
		{
			header(sprintf('X-Robots-Tag: %s,%s', $robotsIndex, $robotsFollow), true);
		}
		$html .= $pageHtml;
		$html .= $this->makeRelNextPrev($allowedGetKeys);
		
		unset($GLOBALS['relPrevUrl'], $GLOBALS['relNextUrl']);
		
		$this->v_output_buffer[] = $html;
	}
	
	protected function makeQueryString($page = 'default')
	{
		$filteredGet = array_filter($_GET, function ($key) use ($page) {
			return !in_array($key, $this->excludeGetParams, true) &&
			       !in_array($key, $this->excludeKeysFromCanonicalPerPageType[$page], true) &&
			       !empty($_GET[$key]);
		}, ARRAY_FILTER_USE_KEY);
		$getParams = http_build_query($filteredGet, '', '&');
		$getParams .= empty($getParams) ? '' : '&';
		return $getParams;
	}
	
	protected function makeRelNextPrev($getArray)
	{
		$html = '';
		$addLanguageParam = $this->activeCodes->count() > 1 && gm_get_conf('USE_SEO_BOOST_LANGUAGE_CODE') !== 'true';
		$languageParam = 'language=' . strtolower($this->languageCode);
		
		if(count(array_intersect($this->noRelPrevNext, $getArray)) === 0)
		{
			if(!empty($GLOBALS['relPrevUrl']))
			{
				$relPrevUrl = $GLOBALS['relPrevUrl'];
				if($addLanguageParam)
				{
					$relPrevUrl .= (strpos($relPrevUrl, '?') !== false ? '&amp;' : '?') . $languageParam;
				}

                $html .= sprintf("<link rel=\"prev\" href=\"%s\" />\n\t\t",
                                 htmlspecialchars($relPrevUrl, ENT_COMPAT, null, false));
			}
			
			if(!empty($GLOBALS['relNextUrl']))
			{
				$relNextUrl = $GLOBALS['relNextUrl'];
				if($addLanguageParam)
				{
					$relNextUrl .= (strpos($relNextUrl, '?') !== false ? '&amp;' : '?') . $languageParam;
				}

                $html .= sprintf("<link rel=\"next\" href=\"%s\" />\n\t\t",
                                 htmlspecialchars($relNextUrl, ENT_COMPAT, null, false));
			}
		}
		return $html;
	}
	
	protected function makeCanonicalLink($file, $params, $languageCode = '', $prefixLanguage = false)
	{
		$params = str_replace('&amp;', '&', $params);
		parse_str($params, $canonicalParamsArray);
		foreach($this->excludeKeysFromCanonical as $excludeKey)
		{
			unset($canonicalParamsArray[$excludeKey]);
		}
		$canonicalParams = http_build_query($canonicalParamsArray, '', '&amp;');
		
		if(!empty($languageCode))
		{
			if(gm_get_conf('USE_SEO_BOOST_LANGUAGE_CODE') === 'true')
			{
				$file = $languageCode . '/' . $file;
			}
			elseif($this->activeCodes->count() > 1)
			{
				$languageParam = 'language=' . $languageCode;
				if($prefixLanguage === true)
				{
					$canonicalParams = $languageParam . '&' . $canonicalParams;
				}
				else
				{
					$canonicalParams .= (empty($params) ? '' : '&amp;') . $languageParam;
				}
			}
		}
		$canonicalUrl = xtc_href_link($file, $canonicalParams, $GLOBALS['request_type'], true, true, false, true, true);
        $html = sprintf("<link rel=\"canonical\" href=\"%s\" />\n\t\t",
                        htmlspecialchars($canonicalUrl, ENT_COMPAT, null, false));
        $html .= sprintf("<meta property=\"og:url\" content=\"%s\">\n\t\t",
                         htmlspecialchars($canonicalUrl, ENT_COMPAT, null, false));
		return $html;
	}
	
	protected function makeAlternateLink($file, $params, $language = 'x-default', $prefixLanguage = false)
	{
		$languageCode = $language === 'x-default' ? $this->defaultLangCode : $language;
		if(gm_get_conf('USE_SEO_BOOST_LANGUAGE_CODE') === 'true')
		{
			$file = $languageCode . '/' . $file;
		}
		else if($prefixLanguage === true)
		{
			$params = 'language=' . $languageCode . '&' . $params;
		}
		else
		{
			$params .= (empty($params) ? '' : '&amp;') . 'language=' . $languageCode;
		}
		$html = sprintf("<link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n\t\t",
		                $language,
                        htmlspecialchars(xtc_href_link($file, $params, $GLOBALS['request_type'], true, true, false, true, true), 
                                         ENT_COMPAT	, null, false));
		return $html;
	}
	
	protected function getProductPageHtml()
	{
		$getParams = $this->makeQueryString('product');
		$html = '';
		$url = FILENAME_PRODUCT_INFO;
		$languageCode     = new LanguageCode(new StringType(strtoupper($_SESSION['language_code'])));
		
		/** @var ProductReadService $productReadService */
		$productReadService = StaticGXCoreLoader::getService('ProductRead');
		$product            = $productReadService->getProductById(new IdType((int)$GLOBALS['actual_products_id']));
		try
		{
			$productLinkParams = xtc_product_link($GLOBALS['actual_products_id'], $product->getName($languageCode));
		}
		catch(InvalidArgumentException $e)
		{
			$productLinkParams = xtc_product_link($GLOBALS['actual_products_id'], '');
		}
		
		if($this->seoBoost->boost_products)
		{
			$altUrl = $this->seoBoost->get_boosted_product_url($GLOBALS['actual_products_id']);
			$altUrl = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
			$html .= $this->makeCanonicalLink($altUrl, $getParams, strtolower($languageCode), true);
		}
		else
		{
			$html .= $this->makeCanonicalLink($url, $getParams . $productLinkParams, strtolower($languageCode), true);
		}
		
		if($this->indexedPage === true && $this->activeCodes->count() > 1)
		{
			if($this->seoBoost->boost_products)
			{
				
				$langCode   = new LanguageCode(new StringType($this->defaultLangCode));
				$languageId = $this->languageProvider->getIdByCode($langCode);
				$altUrl = $this->seoBoost->get_boosted_product_url($GLOBALS['actual_products_id'], '', $languageId);
				$altUrl = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
				$html .= $this->makeAlternateLink($altUrl, $getParams, 'x-default', true);
			}
			else
			{
				$html .= $this->makeAlternateLink($url, $getParams . '&' . $productLinkParams, 'x-default', true);
			}
			
			foreach($this->activeCodes as $code)
			{
				$langCode   = new LanguageCode(new StringType($code->asString()));
				$languageId = $this->languageProvider->getIdByCode($langCode);
				
				if($this->seoBoost->boost_products)
				{
					$altUrl = $this->seoBoost->get_boosted_product_url($GLOBALS['actual_products_id'], '', $languageId);
					$altUrl = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
					$html .= $this->makeAlternateLink($altUrl, $getParams, strtolower($langCode), true);
				}
				else
				{
					try
					{
						$productLinkParams = xtc_product_link($GLOBALS['actual_products_id'], $product->getName($langCode));
					}
					catch(InvalidArgumentException $e)
					{
						$productLinkParams = xtc_product_link($GLOBALS['actual_products_id'], '');
					}
					
					$html .= $this->makeAlternateLink(FILENAME_PRODUCT_INFO, $getParams . $productLinkParams, strtolower($code), true);
				}
			}
		}
		
		return $html;
	}
	
	
	protected function getCategoryPageHtml()
	{
		$getParams = $this->makeQueryString('category');
		$html = '';
		$url = FILENAME_DEFAULT;
		
		/** @var CategoryReadService $categoryReadService */
		$categoryReadService = StaticGXCoreLoader::getService('CategoryRead');
		$category            = $categoryReadService->getCategoryById(new IdType((int)$GLOBALS['current_category_id']));
		$langCode            = new LanguageCode(new StringType(strtoupper($_SESSION['language_code'])));
		$languageId          = $this->languageProvider->getIdByCode($langCode);
		
		try
		{
			$categoryLinkParams = xtc_category_link($GLOBALS['current_category_id'],
			                                        $category->getName($langCode),
			                                        false,
			                                        $languageId);
		}
		catch(InvalidArgumentException $e)
		{
			$categoryLinkParams = '';
		}
		$categoryLinkParams = (!empty($_GET['cPath']) ? '&cPath=' . $_GET['cPath'] : '') . '&amp;' . $categoryLinkParams;
		
		if($this->seoBoost->boost_categories)
		{
			$canonicalUrl = $this->seoBoost->get_boosted_category_url($GLOBALS['current_category_id'], $languageId);
			$canonicalUrl = $canonicalUrl[2] === '/' ? substr($canonicalUrl, 3) : $canonicalUrl;
			$html .= $this->makeCanonicalLink($canonicalUrl, $getParams, strtolower($this->languageCode));
		}
		else
		{
			$html .= $this->makeCanonicalLink($url, $getParams . $categoryLinkParams, strtolower($this->languageCode));
		}
		
		if($this->activeCodes->count() > 1)
		{
			if($this->seoBoost->boost_categories)
			{
				$langCode   = new LanguageCode(new StringType($this->defaultLangCode));
				$languageId = $this->languageProvider->getIdByCode($langCode);
				
				$altUrl = $this->seoBoost->get_boosted_category_url($GLOBALS['current_category_id'], $languageId);
				$altUrl = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
				$html .= $this->makeAlternateLink($altUrl, $getParams, 'x-default');
			}
			else
			{
				$html .= $this->makeAlternateLink($url, $getParams . $categoryLinkParams, 'x-default', true);
			}
			
			foreach($this->activeCodes as $code)
			{
				$langCode   = new LanguageCode(new StringType($code->asString()));
				$languageId = $this->languageProvider->getIdByCode($langCode);
				
				if($this->seoBoost->boost_categories)
				{
					$altUrl = $this->seoBoost->get_boosted_category_url($GLOBALS['current_category_id'], $languageId);
					$altUrl = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
					$html .= $this->makeAlternateLink($altUrl, $getParams, strtolower($langCode));
				}
				else
				{
					try
					{
						$categoryLinkParams = xtc_category_link($GLOBALS['current_category_id'],
						                                        $category->getName($langCode),
						                                        false,
						                                        $languageId);
					}
					catch(InvalidArgumentException $e)
					{
						continue;
					}
					
					$categoryLinkParams = (!empty($_GET['cPath']) ? 'cPath=' . $_GET['cPath'] : '') . '&amp;' . $categoryLinkParams;
					$html .= $this->makeAlternateLink($url, $getParams . $categoryLinkParams, strtolower($langCode), true);
				}
			}
		}
		
		return $html;
	}
	
	
	protected function getContentPageHtml()
	{
		$getParams = $this->makeQueryString('content');
		$html = '';
		$coID = (int)$_GET['coID'];
		$url = FILENAME_CONTENT;
		
		if($this->seoBoost->boost_content)
		{
			$contentId = $this->seoBoost->get_content_id_by_content_group($coID);
			$altUrl    = $this->seoBoost->get_boosted_content_url($contentId);
			$altUrl    = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
			$html .= $this->makeCanonicalLink($altUrl, $getParams, strtolower($this->languageCode));
		}
		else
		{
			$html .= $this->makeCanonicalLink($url, $getParams . 'coID=' . $coID, strtolower($this->languageCode));
		}
		
		if($this->indexedPage === true && $this->activeCodes->count() > 1)
		{
			if($this->seoBoost->boost_content)
			{
				$langCode   = new LanguageCode(new StringType($this->defaultLangCode));
				$languageId = $this->languageProvider->getIdByCode($langCode);
				
				$contentId = $this->seoBoost->get_content_id_by_content_group($coID, $languageId);
				$altUrl    = $this->seoBoost->get_boosted_content_url($contentId, $languageId);
				$altUrl    = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
				
				$html .= $this->makeAlternateLink($altUrl, $getParams, 'x-default');
			}
			else
			{
				$html .= $this->makeAlternateLink($url, $getParams, 'x-default');
			}
			
			foreach($this->activeCodes as $code)
			{
				$langCode   = new LanguageCode(new StringType($code->asString()));
				$languageId = $this->languageProvider->getIdByCode($langCode);
				
				if($this->seoBoost->boost_content)
				{
					$contentId = $this->seoBoost->get_content_id_by_content_group($coID, $languageId);
					$altUrl    = $this->seoBoost->get_boosted_content_url($contentId, $languageId);
					$altUrl    = $altUrl[2] === '/' ? substr($altUrl, 3) : $altUrl;
					$html .= $this->makeAlternateLink($altUrl, $getParams, strtolower($code));
				}
				else
				{
					$html .= $this->makeAlternateLink($url, $getParams . 'coID=' . $coID, strtolower($code));
				}
			}
		}
		return $html;
	}
	
	
	protected function getIndexPageHtml()
	{
		$getParams = $this->makeQueryString('index');
		$html = '';
		$t_index = '';
		
		if(gm_get_conf('SUPPRESS_INDEX_IN_URL') !== 'true')
		{
			$t_index .= 'index.php';
		}
		
		if($this->activeCodes->count() > 1)
		{
			$html .= $this->makeCanonicalLink($t_index, $getParams, strtolower($this->languageCode));
			$html .= $this->makeAlternateLink($t_index, $getParams, 'x-default');
			foreach($this->activeCodes as $code)
			{
				$html .= $this->makeAlternateLink($t_index, $getParams, strtolower($code));
			}
		}
		else
		{
			$html .= $this->makeCanonicalLink($t_index, $getParams, strtolower($this->defaultLangCode));
		}
		return $html;
	}
	
	
	protected function getBoostedLanguageCodeHtml()
	{
		$getParams = $this->makeQueryString('boosted');
		$trimmedRequestUri = $this->getTrimmedRequestUri();
		$html = $this->makeCanonicalLink($trimmedRequestUri, $getParams, strtolower($this->languageCode));
		
		if($this->activeCodes->count() > 1)
		{
			$html .= $this->makeAlternateLink($trimmedRequestUri, $getParams, 'x-default');
			
			foreach($this->activeCodes as $code)
			{
				$html .= $this->makeAlternateLink($trimmedRequestUri, $getParams, strtolower($code));
			}
		}
		return $html;
	}
	
	
	protected function getOtherHtml()
	{
		$getParams = $this->makeQueryString('other');
		$trimmedRequestUri = $this->getTrimmedRequestUri();
		$html = '';
		$hasCanonical = !in_array($trimmedRequestUri, $this->noCanonicalFiles, true);
		if($hasCanonical === true)
		{
			$languageParam = '';
			if($this->activeCodes->count() > 1)
			{
				$languageParam = 'language=' . strtolower($this->languageCode);
			}
			$html .= $this->makeCanonicalLink($trimmedRequestUri, $getParams . $languageParam);
		}
		if($this->activeCodes->count() > 1)
		{
			$html .= $this->makeAlternateLink($trimmedRequestUri, $getParams, 'x-default');
			foreach($this->activeCodes as $code)
			{
				$html .= $this->makeAlternateLink($trimmedRequestUri, $getParams, strtolower($code));
			}
		}
		return $html;
	}
	
	protected function getTrimmedRequestUri()
	{
		$requestUri = gm_get_env_info('REQUEST_URI');
		$requestUri = substr($requestUri, 0, strpos($requestUri, '?') ? : strlen($requestUri));
		$requestUri = substr($requestUri, strlen(DIR_WS_CATALOG));
		$requestUri = preg_replace('/^' . strtolower($this->languageCode) . '/', '', $requestUri);
		$requestUri = $requestUri[0] === '/' ? substr($requestUri, 1) : $requestUri;
		
		return $requestUri;
	}
	
}

