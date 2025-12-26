<?php
/* --------------------------------------------------------------
   UpdateParcelServicesAction.php 2020-08-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\ParcelService\App\Actions;

use Exception;
use Gambio\Admin\Modules\ParcelService\Model\ValueObjects\ParcelServiceDescription;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceFactory;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceReadService;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceWriteService;
use Gambio\Api\Modules\ParcelService\App\ParcelServiceApiRequestParser;
use Gambio\Api\Modules\ParcelService\App\ParcelServiceApiRequestValidator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class UpdateParcelServicesAction
 *
 * @package Gambio\Api\Modules\ParcelService\App\Actions
 */
class UpdateParcelServicesAction
{
    /**
     * @var ParcelServiceApiRequestValidator
     */
    private $requestValidator;
    
    /**
     * @var ParcelServiceApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var ParcelServiceWriteService
     */
    private $writeService;
    
    /**
     * @var ParcelServiceReadService
     */
    private $readService;
    
    /**
     * @var ParcelServiceFactory
     */
    private $factory;
    
    
    /**
     * UpdateParcelServicesAction constructor.
     *
     * @param ParcelServiceApiRequestValidator $requestValidator
     * @param ParcelServiceApiRequestParser    $requestParser
     * @param ParcelServiceWriteService        $writeService
     * @param ParcelServiceReadService         $readService
     * @param ParcelServiceFactory             $factory
     */
    public function __construct(
        ParcelServiceApiRequestValidator $requestValidator,
        ParcelServiceApiRequestParser $requestParser,
        ParcelServiceWriteService $writeService,
        ParcelServiceReadService $readService,
        ParcelServiceFactory $factory
    ) {
        $this->requestValidator = $requestValidator;
        $this->requestParser    = $requestParser;
        $this->writeService     = $writeService;
        $this->readService      = $readService;
        $this->factory          = $factory;
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
        $errors = $this->requestValidator->validatePutRequestBody($request->getParsedBody());
        if (count($errors) > 0) {
            return $response->withStatus(400)->withJson(['errors' => $errors]);
        }
        
        $parcelServices = [];
        foreach ($request->getParsedBody() as $index => $documentData) {
            try {
                $descriptions = array_map(function (array $descriptionData): ParcelServiceDescription {
                    return $this->factory->createParcelServiceDescription($descriptionData['languageCode'],
                                                                          $descriptionData['url'],
                                                                          $descriptionData['comment']);
                },
                    $documentData['descriptions']);
                
                $parcelService = $this->readService->getParcelServiceById($documentData['id']);
                $parcelService->changeName($documentData['name']);
                $parcelService->changeDescriptions($this->factory->createParcelServiceDescriptions(...$descriptions));
                if ($documentData['isDefault'] === true) {
                    $parcelService->setAsDefault();
                }
                $parcelServices[] = $parcelService;
            } catch (Exception $exception) {
                $errors[$index][] = $exception->getMessage();
            }
        }
        
        if (count($errors) > 0) {
            return $response->withStatus(422)->withJson(['errors' => $errors]);
        }
        
        $this->writeService->storeParcelServices(...$parcelServices);
        
        return $response->withStatus(204);
    }
}