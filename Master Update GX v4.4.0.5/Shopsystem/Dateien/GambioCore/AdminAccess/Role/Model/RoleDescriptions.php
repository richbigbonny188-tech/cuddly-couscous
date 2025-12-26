<?php
/* --------------------------------------------------------------
   RoleDescriptions.php 2020-10-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Model;

use Webmozart\Assert\Assert;

/**
 * Class RoleDescriptions
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class RoleDescriptions implements \Gambio\Core\AdminAccess\Role\RoleDescriptions
{
    /**
     * @var array<int, string>
     */
    private $descriptions;
    
    
    /**
     * RoleDescriptions constructor.
     */
    private function __construct()
    {
        $this->descriptions = [];
    }
    
    
    /**
     * @param array $descriptions
     *
     * @return RoleDescriptions
     */
    public static function create(array $descriptions): RoleDescriptions
    {
        $collection = new self();
        foreach ($descriptions as $languageId => $description) {
            $collection->addDescription($languageId, $description);
        }
        
        return $collection;
    }
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function getDescription(int $languageId): string
    {
        return $this->descriptions[$languageId] ?? '';
    }
    
    
    /**
     * @param int    $languageId
     * @param string $description
     */
    public function addDescription(int $languageId, string $description): void
    {
        Assert::greaterThan($languageId, 0, 'Language ID need to be greater than 0. Got: %s');
        
        $this->descriptions[$languageId] = $description;
    }
}