<?php
/* --------------------------------------------------------------
   FetchAllTrackingCodesAction.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\TrackingCode\App\Actions;

use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeFilterService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Gambio\Api\Application\Responses\ResponseDataTrimmerTrait;
use Gambio\Api\Modules\TrackingCode\App\TrackingCodeApiRequestParser;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class FetchAllTrackingCodesAction
 *
 * @package Gambio\Api\Modules\TrackingCode\App\Actions
 */
class FetchAllTrackingCodesAction
{
    use CreateApiMetaDataTrait;
    use ResponseDataTrimmerTrait;
    
    /**
     * @var TrackingCodeApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var TrackingCodeFilterService
     */
    private $service;
    
    
    /**
     * FetchAllTrackingCodesAction constructor.
     *
     * @param TrackingCodeApiRequestParser $requestParser
     * @param TrackingCodeFilterService    $service
     */
    public function __construct(TrackingCodeApiRequestParser $requestParser, TrackingCodeFilterService $service)
    {
        $this->requestParser = $requestParser;
        $this->service       = $service;
    }
    
    
    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fields  = $this->requestParser->getFields($request);
        $filters = $this->requestParser->getFilters($request);
        $sorting = $this->requestParser->getSorting($request);
        $page    = $this->requestParser->getPage($request);
        $limit   = $this->requestParser->getPerPage($request);
        $offset  = $limit * ($page - 1);
        
        $trackingCodes = $this->service->filterTrackingCodes($filters, $sorting, $limit, $offset);
        $totalItems    = $this->service->getTrackingCodesTotalCount($filters);
        $metaData      = $this->createApiCollectionMetaData($page,
                                                            $limit,
                                                            $totalItems,
                                                            $this->requestParser->getResourceUrlFromRequest($request),
                                                            $request->getQueryParams());
        
        $responseData = $trackingCodes->toArray();
        if (count($fields) > 0) {
            $responseData = $this->trimCollectionData($responseData, $fields);
        }
        
        return $response->withJson([
                                       'data'  => $responseData,
                                       '_meta' => $metaData,
                                   ]);
    }
}