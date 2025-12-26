<?php
/* --------------------------------------------------------------
   FetchAllWithdrawalsAction.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\Withdrawal\App\Actions;

use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFilterService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Gambio\Api\Application\Responses\ResponseDataTrimmerTrait;
use Gambio\Api\Modules\Withdrawal\App\WithdrawalApiRequestParser;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class FetchAllWithdrawalsAction
 *
 * @package Gambio\Api\Modules\Withdrawal\App\Actions
 */
class FetchAllWithdrawalsAction
{
    use CreateApiMetaDataTrait;
    use ResponseDataTrimmerTrait;
    
    /**
     * @var WithdrawalApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var WithdrawalFilterService
     */
    private $service;
    
    
    /**
     * FetchAllWithdrawalsAction constructor.
     *
     * @param WithdrawalApiRequestParser $requestParser
     * @param WithdrawalFilterService    $service
     */
    public function __construct(WithdrawalApiRequestParser $requestParser, WithdrawalFilterService $service)
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
        
        $withdrawals = $this->service->filterWithdrawals($filters, $sorting, $limit, $offset);
        $totalItems  = $this->service->getWithdrawalsTotalCount($filters);
        $metaData    = $this->createApiCollectionMetaData($page,
                                                          $limit,
                                                          $totalItems,
                                                          $this->requestParser->getResourceUrlFromRequest($request),
                                                          $request->getQueryParams());
        
        $responseData = $withdrawals->toArray();
        if (count($fields) > 0) {
            $responseData = $this->trimCollectionData($responseData, $fields);
        }
        
        return $response->withJson([
                                       'data'  => $responseData,
                                       '_meta' => $metaData,
                                   ]);
    }
}