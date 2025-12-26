<?php
/* --------------------------------------------------------------
   TrackingCodeApiRequestParser.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\TrackingCode\App;

use Exception;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TrackingCodeApiRequestParser
 *
 * @package Gambio\Api\Modules\TrackingCode\App
 */
class TrackingCodeApiRequestParser
{
    /**
     * @var TrackingCodeFactory
     */
    private $factory;
    
    
    /**
     * TrackingCodeApiRequestParser constructor.
     *
     * @param TrackingCodeFactory $factory
     */
    public function __construct(TrackingCodeFactory $factory)
    {
        $this->factory = $factory;
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return int
     */
    public function getPerPage(ServerRequestInterface $request): int
    {
        return (int)$request->getQueryParam('per-page', 25);
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return int
     */
    public function getPage(ServerRequestInterface $request): int
    {
        return (int)$request->getQueryParam('page', 1);
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function getFields(ServerRequestInterface $request): array
    {
        $fields = $request->getQueryParam('fields');
        
        return ($fields === null) ? [] : explode(',', $fields);
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function getFilters(ServerRequestInterface $request): array
    {
        $filters      = $request->getQueryParam('filter', []);
        $createdAfter = $request->getQueryParam('created-after');
        if ($createdAfter !== null) {
            $filters['createdOn'] = 'gt|' . $createdAfter;
        }
        
        return $filters;
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getSorting(ServerRequestInterface $request): string
    {
        return $request->getQueryParam('sort', '');
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getResourceUrlFromRequest(ServerRequestInterface $request): string
    {
        return $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $request->getUri()->getPath();
    }
    
    
    /**
     * @param ServerRequestInterface $request
     * @param array                  $errors
     *
     * @return array
     */
    public function parseTrackingCodeDataForCreation(ServerRequestInterface $request, &$errors = []): array
    {
        $orderIds             = [];
        $codes                = [];
        $parcelServiceDetails = [];
        
        foreach ($request->getParsedBody() as $index => $documentData) {
            try {
                $orderIds[]             = $documentData['orderId'];
                $codes[]                = $documentData['code'];
                $parcelServiceDetails[] = $this->factory->createParcelServiceDetails($documentData['parcelService']['id'],
                                                                                     $documentData['parcelService']['languageCode'],
                                                                                     $documentData['parcelService']['name'],
                                                                                     $documentData['parcelService']['url'],
                                                                                     $documentData['parcelService']['comment']);
            } catch (Exception $exception) {
                $errors[$index][] = $exception->getMessage();
            }
        }
        
        return [
            'orderIds'             => $orderIds,
            'codes'                => $codes,
            'parcelServiceDetails' => $parcelServiceDetails,
        ];
    }
}