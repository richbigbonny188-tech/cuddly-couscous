<?php
/* --------------------------------------------------------------
   WithdrawalsController.inc.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright © 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFactory;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFilterService;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalReadService;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalWriteService;

/**
 * Class WithdrawalsController
 */
class WithdrawalsController extends AdminHttpViewController
{
    /**
     * @var WithdrawalFilterService
     */
    protected $filterService;
    
    
    /**
     * @var WithdrawalReadService
     */
    protected $readService;
    
    
    /**
     * @var WithdrawalWriteService
     */
    protected $writeService;
    
    
    /**
     * @var WithdrawalFactory
     */
    protected $factory;
    
    
    /**
     * Initialization of VPE controller
     */
    public function init()
    {
        $this->filterService = LegacyDependencyContainer::getInstance()->get(WithdrawalFilterService::class);
        $this->readService   = LegacyDependencyContainer::getInstance()->get(WithdrawalReadService::class);
        $this->writeService  = LegacyDependencyContainer::getInstance()->get(WithdrawalWriteService::class);
        $this->factory       = LegacyDependencyContainer::getInstance()->get(WithdrawalFactory::class);
    }
    
    
    /**
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionDefault(): AdminLayoutHttpControllerResponse
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'withdrawals', $_SESSION['languages_id']);
        
        $title = new NonEmptyStringType($languageTextManager->get_text('title'));
        
        $template = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN . '/html/content/withdrawals/overview.html'));
        
        $assets = MainFactory::create('AssetCollection');
        $assets->add(MainFactory::create('Asset', 'admin_buttons.lang.inc.php'));
        $assets->add(MainFactory::create('Asset', 'withdrawals.lang.inc.php'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, null, $assets);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionGetWithdrawals(): JsonHttpControllerResponse
    {
        $_SESSION['coo_page_token']->is_valid($this->_getPostData('page_token'));
        
        try {
            $offset    = (int)$this->_getPostData('offset');
            $offset    = ($offset >= 0) ? $offset : 0;
            $limit     = (int)$this->_getPostData('limit');
            $limit     = ($limit >= 1) ? $limit : 10;
            $sortBy    = $this->_getPostData('sortBy') ?? 'id';
            $sortOrder = $this->_getPostData('sortOrder') ?? 'asc';
            $sorting   = (($sortOrder === 'desc') ? '-' : '') . $sortBy;
            
            $data = [];
            foreach ($this->filterService->filterWithdrawals([], $sorting, $limit, $offset) as $withdrawal) {
                $data[] = [
                    'id'         => $withdrawal->id(),
                    'date'       => $withdrawal->date(DATE_FORMAT),
                    'customer'   => $withdrawal->customerFirstName() . ' ' . $withdrawal->customerLastName(),
                    'customerId' => $withdrawal->customerId(),
                    'orderId'    => $withdrawal->orderId(),
                ];
            }
            $total = $this->filterService->getWithdrawalsTotalCount([]);
            
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['success' => true, 'withdrawals' => $data, 'total' => $total]);
        } catch (Exception $exception) {
            return MainFactory::create('JsonHttpControllerResponse',
                                       [
                                           'success'     => false,
                                           'withdrawals' => [],
                                           'total'       => 0,
                                           'error'       => $exception->getMessage()
                                       ]);
        }
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionGetWithdrawal(): JsonHttpControllerResponse
    {
        $_SESSION['coo_page_token']->is_valid($this->_getPostData('page_token'));
        
        try {
            $id         = (int)$this->_getPostData('id');
            $withdrawal = $this->readService->getWithdrawalById($id);
            $data       = [
                'id'             => $withdrawal->id(),
                'order'          => [
                    'id'           => $withdrawal->orderId(),
                    'creationDate' => ($withdrawal->orderCreationDate() !== null) ? date(DATE_FORMAT,
                                                                                         strtotime($withdrawal->orderCreationDate())) : '',
                    'deliveryDate' => ($withdrawal->orderDeliveryDate() !== null) ? date(DATE_FORMAT,
                                                                                         strtotime($withdrawal->orderDeliveryDate())) : '',
                ],
                'customer'       => [
                    'id'        => $withdrawal->customerId(),
                    'gender'    => $withdrawal->customerGender(),
                    'firstName' => $withdrawal->customerFirstName(),
                    'lastName'  => $withdrawal->customerLastName(),
                    'address'   => [
                        'street'   => $withdrawal->customerStreet(),
                        'postcode' => $withdrawal->customerPostcode(),
                        'city'     => $withdrawal->customerCity(),
                        'country'  => $withdrawal->customerCountry(),
                    ],
                    'email'     => $withdrawal->customerEmail(),
                ],
                'date'           => $withdrawal->date(DATE_FORMAT),
                'content'        => $withdrawal->content(),
                'createdByAdmin' => $withdrawal->wasCreatedByAdmin(),
                'createdOn'      => $withdrawal->createdOn(DATE_FORMAT),
            ];
            
            return MainFactory::create('JsonHttpControllerResponse', ['success' => true, 'withdrawal' => $data]);
        } catch (Exception $exception) {
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['success' => false, 'error' => $exception->getMessage()]);
        }
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionChangeOrderId(): JsonHttpControllerResponse
    {
        $_SESSION['coo_page_token']->is_valid($this->_getPostData('page_token'));
        
        try {
            $id         = (int)$this->_getPostData('id');
            $orderId    = (int)$this->_getPostData('orderId');
            $withdrawal = $this->readService->getWithdrawalById($id);
            
            $withdrawal->changeOrderId($this->factory->createOrderId($orderId));
            $this->writeService->storeWithdrawals($withdrawal);
            
            return MainFactory::create('JsonHttpControllerResponse', ['success' => true]);
        } catch (Exception $exception) {
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['success' => false, 'error' => $exception->getMessage()]);
        }
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionDeleteWithdrawal(): JsonHttpControllerResponse
    {
        $_SESSION['coo_page_token']->is_valid($this->_getPostData('page_token'));
        
        try {
            $id = (int)$this->_getPostData('id');
            $this->writeService->deleteWithdrawals($id);
            
            return MainFactory::create('JsonHttpControllerResponse', ['success' => true]);
        } catch (Exception $exception) {
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['success' => false, 'error' => $exception->getMessage()]);
        }
    }
}