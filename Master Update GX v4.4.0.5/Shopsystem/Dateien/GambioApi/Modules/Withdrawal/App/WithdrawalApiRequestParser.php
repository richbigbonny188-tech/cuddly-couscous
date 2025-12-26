<?php
/* --------------------------------------------------------------
   WithdrawalApiRequestParser.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\Withdrawal\App;

use Exception;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class WithdrawalApiRequestParser
 *
 * @package Gambio\Api\Modules\Withdrawal\App
 */
class WithdrawalApiRequestParser
{
    /**
     * @var WithdrawalFactory
     */
    private $factory;
    
    
    /**
     * WithdrawalApiRequestParser constructor.
     *
     * @param WithdrawalFactory $factory
     */
    public function __construct(WithdrawalFactory $factory)
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
     * @return array<int,int>
     */
    public function parseChangedOrderIds(ServerRequestInterface $request, &$errors = []): array
    {
        $changedOrderIds = [];
        foreach ($request->getParsedBody() as $documentData) {
            $changedOrderIds[] = [
                'withdrawalId' => $documentData['id'],
                'newOrderId'   => $documentData['order']['id'],
            ];
        }
        
        return $changedOrderIds;
    }
    
    
    /**
     * @param ServerRequestInterface $request
     * @param array                  $errors
     *
     * @return array
     */
    public function parseWithdrawalDataForCreation(ServerRequestInterface $request, &$errors = []): array
    {
        $orders              = [];
        $customers           = [];
        $dates               = [];
        $contents            = [];
        $createdByAdminFlags = [];
        
        foreach ($request->getParsedBody() as $index => $documentData) {
            try {
                $orders[] = $this->factory->createOrderDetails($documentData['order']['id'],
                                                               $documentData['order']['creationDate'],
                                                               $documentData['order']['deliveryDate']);
                
                $address = $this->factory->createCustomerAddress($documentData['customer']['address']['street'],
                                                                 $documentData['customer']['address']['postcode'],
                                                                 $documentData['customer']['address']['city'],
                                                                 $documentData['customer']['address']['country']);
                
                $customers[] = $this->factory->createCustomerDetails($documentData['customer']['email'],
                                                                     $address,
                                                                     $documentData['customer']['id'],
                                                                     $documentData['customer']['gender'],
                                                                     $documentData['customer']['firstName'],
                                                                     $documentData['customer']['lastName']);
                
                $dates[]               = $documentData['date'];
                $contents[]            = $documentData['content'];
                $createdByAdminFlags[] = $documentData['createdByAdmin'];
            } catch (Exception $exception) {
                $errors[$index][] = $exception->getMessage();
            }
        }
        
        return [
            'orders'              => $orders,
            'customers'           => $customers,
            'dates'               => $dates,
            'contents'            => $contents,
            'createdByAdminFlags' => $createdByAdminFlags,
        ];
    }
}