<?php
/*--------------------------------------------------------------------------------------------------
    VpeBuilderInterface.php 2021-01-25
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\SellingUnit\Unit\Builders\Exceptions\UnfinishedBuildException;
use Gambio\Shop\SellingUnit\Unit\Entities\Price;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

interface VpeBuilderInterface
{
    /**
     * @return self
     */
    public static function create(): self;
    
    
    /**
     * @return Vpe
     * @throws UnfinishedBuildException
     */
    public function build(): Vpe;
    
    
    /**
     * @return self
     */
    public function reset(): self;
    
    
    /**
     * @param int $id
     *
     * @return $this
     */
    public function withId(int $id): self;
    
    
    /**
     * @param string $name
     *
     * @return $this
     */
    public function withName(string $name): self;
    
    
    /**
     * @param float $value
     *
     * @return $this
     */
    public function withValue(float $value): self;
}
