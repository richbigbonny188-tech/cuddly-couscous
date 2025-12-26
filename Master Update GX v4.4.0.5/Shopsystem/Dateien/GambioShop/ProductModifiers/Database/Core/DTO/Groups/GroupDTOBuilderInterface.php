<?php
/*--------------------------------------------------------------------------------------------------
    GroupDTOBuilderInterface.php 2020-10-27
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups;

use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupIdentifierInterface;

/**
 * Interface GroupDTOBuilderInterface
 * @package Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups
 */
interface GroupDTOBuilderInterface
{
    /**
     * @param GroupIdentifierInterface $id
     *
     * @return mixed
     */
    public function withId(GroupIdentifierInterface $id): GroupDTOBuilderInterface;
    
    
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function withName(string $name): GroupDTOBuilderInterface;
    
    
    /**
     * @param string $type
     *
     * @return mixed
     */
    public function withType(string $type): GroupDTOBuilderInterface;
    
    
    /**
     * @param bool $selectable
     *
     * @return mixed
     */
    public function withSelectable(bool $selectable): GroupDTOBuilderInterface;
    
    
    /**
     * @param string $source
     *
     * @return GroupDTOBuilderInterface
     */
    public function withSource(string $source): GroupDTOBuilderInterface;
    
    
    /**
     * @return GroupDTO
     */
    public function build(): GroupDTO;
}
