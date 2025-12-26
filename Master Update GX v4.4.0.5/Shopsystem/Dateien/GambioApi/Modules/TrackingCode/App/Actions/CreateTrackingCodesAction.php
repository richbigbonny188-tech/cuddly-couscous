<?php
/* --------------------------------------------------------------
   CreateTrackingCodesAction.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\TrackingCode\App\Actions;

use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeWriteService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Gambio\Api\Modules\TrackingCode\App\TrackingCodeApiRequestParser;
use Gambio\Api\Modules\TrackingCode\App\TrackingCodeApiRequestValidator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class CreateTrackingCodesAction
 *
 * @package Gambio\Api\Modules\TrackingCode\App\Actions
 */
class CreateTrackingCodesAction
{
    use CreateApiMetaDataTrait;
    
    /**
     * @var TrackingCodeApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var TrackingCodeApiRequestValidator
     */
    private $requestValidator;
    
    /**
     * @var TrackingCodeWriteService
     */
    private $service;
    
    
    /**
     * CreateTrackingCodesAction constructor.
     *
     * @param TrackingCodeApiRequestParser    $requestParser
     * @param TrackingCodeApiRequestValidator $requestValidator
     * @param TrackingCodeWriteService        $service
     */
    public function __construct(
        TrackingCodeApiRequestParser $requestParser,
        TrackingCodeApiRequestValidator $requestValidator,
        TrackingCodeWriteService $service
    ) {
        $this->requestParser    = $requestParser;
        $this->requestValidator = $requestValidator;
        $this->service          = $service;
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
        $errors = $this->requestValidator->validatePostRequestBody($request->getParsedBody());
        if (count($errors) > 0) {
            return $response->withStatus(400)->withJson(['errors' => $errors]);
        }
        
        $trackingCodesData = $this->requestParser->parseTrackingCodeDataForCreation($request, $errors);
        if (count($errors) > 0) {
            return $response->withStatus(422)->withJson(['errors' => $errors]);
        }
        $ids = $this->service->createMultipleTrackingCodes($trackingCodesData['orderIds'],
                                                           $trackingCodesData['codes'],
                                                           $trackingCodesData['parcelServiceDetails']);
        
        $links   = [];
        $baseUrl = rtrim($this->requestParser->getResourceUrlFromRequest($request), '/');
        foreach ($ids as $id) {
            $links[] = $baseUrl . '/parcel-services/' . $id->value();
        }
        
        $metaData = $this->createApiMetaData($links);
        
        return $response->withJson([
                                       'data'  => $ids->toArray(),
                                       '_meta' => $metaData,
                                   ],
                                   201);
    }
}