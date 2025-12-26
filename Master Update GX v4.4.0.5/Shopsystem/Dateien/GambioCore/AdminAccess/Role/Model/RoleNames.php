<?php
/* --------------------------------------------------------------
   RoleNames.php 2020-05-29
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
 * Class RoleNames
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class RoleNames implements \Gambio\Core\AdminAccess\Role\RoleNames
{
    /**
     * @var array<int, string>
     */
    private $names;
    
    
    /**
     * RoleNames constructor.
     */
    private function __construct()
    {
        $this->names = [];
    }
    
    
    /**
     * @param array $names
     *
     * @return RoleNames
     */
    public static function create(array $names): RoleNames
    {
        $collection = new self();
        foreach ($names as $languageId => $name) {
            $collection->addName($languageId, $name);
        }
        
        return $collection;
    }
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function getName(int $languageId): string
    {
        return $this->names[$languageId] ?? '';
    }
    
    
    /**
     * @param int    $languageId
     * @param string $name
     */
    public function addName(int $languageId, string $name): void
    {
        Assert::greaterThan($languageId, 0, 'Language ID need to be greater than 0. Got: %s');
        Assert::notWhitespaceOnly($name, 'Role name can not be empty.');
        
        $this->names[$languageId] = $name;
    }
}