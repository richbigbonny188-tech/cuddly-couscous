<?php
/* --------------------------------------------------------------
   CreateParcelServicesAction.php 2020-08-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\ParcelService\App\Actions;

use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceWriteService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Gambio\Api\Modules\ParcelService\App\ParcelServiceApiRequestParser;
use Gambio\Api\Modules\ParcelService\App\ParcelServiceApiRequestValidator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class CreateParcelServicesAction
 *
 * @package Gambio\Api\Modules\ParcelService\App\Actions
 */
class CreateParcelServicesAction
{
    use CreateApiMetaDataTrait;
    
    /**
     * @var ParcelServiceApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var ParcelServiceApiRequestValidator
     */
    private $requestValidator;
    
    /**
     * @var ParcelServiceWriteService
     */
    private $service;
    
    
    /**
     * CreateParcelServicesAction constructor.
     *
     * @param ParcelServiceApiRequestParser    $requestParser
     * @param ParcelServiceApiRequestValidator $requestValidator
     * @param ParcelServiceWriteService        $service
     */
    public function __construct(
        ParcelServiceApiRequestParser $requestParser,
        ParcelServiceApiRequestValidator $requestValidator,
        ParcelServiceWriteService $service
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
        
        $parcelServicesData = $this->requestParser->parseParcelServiceDataForCreation($request, $errors);
        if (count($errors) > 0) {
            return $response->withStatus(422)->withJson(['errors' => $errors]);
        }
        $ids = $this->service->createMultipleParcelServices($parcelServicesData['names'],
                                                            $parcelServicesData['descriptions'],
                                                            $parcelServicesData['isDefaultFlags']);
        
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