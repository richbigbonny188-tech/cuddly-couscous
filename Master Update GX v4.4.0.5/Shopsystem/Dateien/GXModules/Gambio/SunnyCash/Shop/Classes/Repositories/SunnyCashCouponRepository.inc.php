<?php
/* --------------------------------------------------------------
   SunnyCashCouponRepository.inc.php 2020-10-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class SunnyCashCouponRepository
 */
class SunnyCashCouponRepository implements SunnyCashCouponRepositoryInterface
{
    /**
     * @var SunnyCashCouponReaderInterface $couponReader
     */
    protected $couponReader;
    
    /**
     * @var SunnyCashCacheManagerInterface $cacheManager
     */
    protected $cacheManager;
    
    
    /**
     * SunnyCashCouponRepository constructor.
     *
     * @param SunnyCashCouponReaderInterface $couponReader
     * @param SunnyCashCacheManagerInterface $cacheManager
     */
    public function __construct(
        SunnyCashCouponReaderInterface $couponReader,
        SunnyCashCacheManagerInterface $cacheManager
    ) {
        $this->couponReader = $couponReader;
        $this->cacheManager = $cacheManager;
    }
    
    
    /**
     * Gets a coupon by its ID.
     *
     * @param IdType $id
     *
     * @return SunnyCashCoupon
     */
    public function getCouponById(IdType $id)
    {
        $this->cacheManager->checkAndPerformRefresh();
        
        return $this->couponReader->getCouponById($id);
    }
    
    
    /**
     * Gets all available SunnyCash coupons.
     *
     * @param IntType $limit
     * @param IntType $offset
     *
     * @return SunnyCashCouponCollection
     */
    public function getAllCoupons(IntType $limit, IntType $offset)
    {
        $this->cacheManager->checkAndPerformRefresh();
        
        return $this->couponReader->getAllCoupons($limit, $offset);
    }
    
    
    /**
     * Gets the total number of available SunnyCash coupons.
     *
     * @return int
     */
    public function getCouponQuantity()
    {
        $this->cacheManager->checkAndPerformRefresh();
        
        return $this->couponReader->getCouponQuantity();
    }
}