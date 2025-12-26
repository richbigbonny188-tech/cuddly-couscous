<?php
/* --------------------------------------------------------------
 TemplateDataCollection.php 2020-09-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine\Collection;

use Gambio\Core\TemplateEngine\LayoutData;

/**
 * Class TemplateDataCollection
 * @package Gambio\Core\TemplateEngine\Collection
 */
class LayoutDataCollection implements LayoutData
{
    /**
     * @var array
     */
    private $data = [];
    
    
    /**
     * @inheritDoc
     */
    public function assign(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
    
    
    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->data;
    }
}