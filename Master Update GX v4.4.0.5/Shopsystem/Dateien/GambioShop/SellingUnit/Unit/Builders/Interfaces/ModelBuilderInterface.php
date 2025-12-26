<?php


namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\SellingUnit\Unit\ValueObjects\Model;

/**
 * Interface ModelBuilderInterface
 *
 * @package Gambio\Shop\SellingUnit\Unit\Builders\Interfaces
 */
interface ModelBuilderInterface
{
    /**
     * @return ModelBuilderInterface
     */
    public function wipeData(): ModelBuilderInterface;

    /**
     * @param Model $model
     * @param int $pos
     *
     * @return ModelBuilderInterface
     */
    public function withModelAtPos(Model $model, int $pos): ModelBuilderInterface;

    /**
     * @return Model
     */
    public function build() : Model;

}