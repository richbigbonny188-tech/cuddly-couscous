<?php


namespace Gambio\Shop\SellingUnit\Unit\Builders;


use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\ModelBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Model;

class ModelBuilder implements ModelBuilderInterface
{
    protected $modelParts = [];

    /**
     * @inheritDoc
     */
    public function wipeData(): ModelBuilderInterface
    {
        $this->modelParts = [];
        return $this;

    }

    /**
     * @inheritDoc
     */
    public function withModelAtPos(Model $model, int $pos): ModelBuilderInterface
    {
        $this->modelParts[$pos] = $model;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function build(): Model
    {
        $data      = [];
        ksort($this->modelParts);
        /** @var Model $part */
        foreach ($this->modelParts as $part) {
            $data[] = $part->value();
        }
        $this->modelParts = [];
        return new Model(implode('-', $data));
    }
}