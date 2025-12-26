<?php
/*--------------------------------------------------------------------------------------------------
    PresentationMapperFactory.php 2020-01-23
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Database\Presentation;

use Gambio\Shop\ProductModifiers\Database\Core\Factories\Interfaces\PresentationMapperFactoryInterface;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Implementations\DropDownSelectMapper;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Implementations\ImageMapper;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Implementations\ImageMapperSettings;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Implementations\RadioMapper;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Implementations\BoxedTextMapper;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Implementations\TextMapper;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Interfaces\PresentationMapperInterface;
use Gambio\Shop\ProductModifiers\Presentation\Implementations\Image\Builders\ImageInfoBuilder;
use Gambio\Shop\ProductModifiers\Presentation\Implementations\Radio\Builders\RadioInfoBuilder;
use Gambio\Shop\ProductModifiers\Presentation\Implementations\SelectDropDown\Builders\SelectDropDownInfoBuilder;
use Gambio\Shop\ProductModifiers\Presentation\Implementations\SquareText\Builders\BoxedTextInfoBuilder;
use Gambio\Shop\ProductModifiers\Presentation\Implementations\Text\Builders\TextInfoBuilder;

/**
 * Class PresentationMapperFactory
 * @package Gambio\Shop\ProductModifiers\Database\Presentation
 */
class PresentationMapperFactory implements PresentationMapperFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createMapperChain(): PresentationMapperInterface
    {

        $image     = new ImageMapper(New ImageInfoBuilder(), new ImageMapperSettings());
        $radio     = new RadioMapper(New RadioInfoBuilder());
        $dropdown  = new DropDownSelectMapper(New SelectDropDownInfoBuilder());
        $text      = new TextMapper(New TextInfoBuilder());
        $boxedText = new BoxedTextMapper(New BoxedTextInfoBuilder());

        $image->setNext($radio);
        $radio->setNext($dropdown);
        $dropdown->setNext($text);
        $text->setNext($boxedText);

        return $image;
    }
    public function __construct()
    {
        $q = 1;
    }
}