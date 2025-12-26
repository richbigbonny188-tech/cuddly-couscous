<?php
/* --------------------------------------------------------------
   GroupNames.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Model;

use Webmozart\Assert\Assert;

/**
 * Class GroupNames
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupNames implements \Gambio\Core\AdminAccess\Group\GroupNames
{
    /**
     * @var array<int, string>
     */
    private $names;
    
    
    /**
     * GroupNames constructor.
     */
    private function __construct()
    {
        $this->names = [];
    }
    
    
    /**
     * @param array $names
     *
     * @return GroupNames
     */
    public static function create(array $names): GroupNames
    {
        $collection = new self();
        foreach ($names as $languageId => $name) {
            $collection->addName($languageId, $name);
        }
        
        return $collection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getName(int $languageId): string
    {
        return $this->names[$languageId] ?? '';
    }
    
    
    /**
     * @inheritDoc
     */
    public function addName(int $languageId, string $name): void
    {
        Assert::greaterThan($languageId, 0, 'Language ID need to be greater than 0. Got: %s');
        Assert::notWhitespaceOnly($name, 'Name can not be empty.');
        
        $this->names[$languageId] = $name;
    }
}