<?php
/* --------------------------------------------------------------
   FetchAllParcelServicesAction.php 2020-08-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\ParcelService\App\Actions;

use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceFilterService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Gambio\Api\Application\Responses\ResponseDataTrimmerTrait;
use Gambio\Api\Modules\ParcelService\App\ParcelServiceApiRequestParser;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class FetchAllParcelServicesAction
 *
 * @package Gambio\Api\Modules\ParcelService\App\Actions
 */
class FetchAllParcelServicesAction
{
    use CreateApiMetaDataTrait;
    use ResponseDataTrimmerTrait;
    
    /**
     * @var ParcelServiceApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var ParcelServiceFilterService
     */
    private $service;
    
    
    /**
     * FetchAllParcelServicesAction constructor.
     *
     * @param ParcelServiceApiRequestParser $requestParser
     * @param ParcelServiceFilterService    $service
     */
    public function __construct(ParcelServiceApiRequestParser $requestParser, ParcelServiceFilterService $service)
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
        
        $parcelServices = $this->service->filterParcelServices($filters, $sorting, $limit, $offset);
        $totalItems     = $this->service->getParcelServicesTotalCount($filters);
        $metaData       = $this->createApiCollectionMetaData($page,
                                                             $limit,
                                                             $totalItems,
                                                             $this->requestParser->getResourceUrlFromRequest($request),
                                                             $request->getQueryParams());
        
        $responseData = $parcelServices->toArray();
        if (count($fields) > 0) {
            $responseData = $this->trimCollectionData($responseData, $fields);
        }
        
        return $response->withJson([
                                       'data'  => $responseData,
                                       '_meta' => $metaData,
                                   ]);
    }
}