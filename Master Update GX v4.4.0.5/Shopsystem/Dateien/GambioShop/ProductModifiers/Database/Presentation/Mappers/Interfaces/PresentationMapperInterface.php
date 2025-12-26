<?php
/*--------------------------------------------------------------------------------------------------
    PresentationMapperInterface.php 2020-01-23
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Interfaces;

use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTO;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTO;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Exceptions\PresentationMapperNotFoundException;
use Gambio\Shop\ProductModifiers\Presentation\Core\PresentationInfoInterface;
use Gambio\Shop\ProductModifiers\Presentation\Core\PresentationTypeInterface;

/**
 * Interface PresentationMapperInterface
 * @package Gambio\Shop\ProductModifiers\Database\Presentation\Mappers
 */
interface PresentationMapperInterface
{
    /**
     * @param ModifierDTO $dto
     *
     * @return PresentationInfoInterface
     * @throws PresentationMapperNotFoundException
     */
    public function createPresentationInfo(ModifierDTO $dto): PresentationInfoInterface;
    
    
    /**
     * @param GroupDTO $dto
     *
     * @return PresentationTypeInterface
     */
    public function createPresentationType(GroupDTO $dto): PresentationTypeInterface;
    
    /**
     * @param PresentationMapperInterface $next
     */
    public function setNext(PresentationMapperInterface $next): void;
}