<?php
/* --------------------------------------------------------------
   GroupDescriptions.php 2020-05-29
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
 * Class GroupDescriptions
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupDescriptions implements \Gambio\Core\AdminAccess\Group\GroupDescriptions
{
    /**
     * @var array<int, string>
     */
    private $descriptions;
    
    
    /**
     * GroupDescriptions constructor.
     */
    private function __construct()
    {
        $this->descriptions = [];
    }
    
    
    /**
     * @param array $descriptions
     *
     * @return GroupDescriptions
     */
    public static function create(array $descriptions): GroupDescriptions
    {
        $collection = new self();
        foreach ($descriptions as $languageId => $name) {
            $collection->addDescription($languageId, $name);
        }
        
        return $collection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getDescription(int $languageId): string
    {
        return $this->descriptions[$languageId] ?? '';
    }
    
    
    /**
     * @inheritDoc
     */
    public function addDescription(int $languageId, string $description): void
    {
        Assert::greaterThan($languageId, 0, 'Language ID need to be greater than 0. Got: %s');
        Assert::notWhitespaceOnly($description, 'Description can not be empty.');
        
        $this->descriptions[$languageId] = $description;
    }
}