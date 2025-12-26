<?php


namespace Gambio\Shop\Properties\ProductModifiers\Database\Repository;


use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTO;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTO;
use Gambio\Shop\ProductModifiers\Database\Core\Factories\Interfaces\PresentationMapperFactoryInterface;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Exceptions\PresentationMapperNotFoundException;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Interfaces\PresentationMapperInterface;
use Gambio\Shop\ProductModifiers\Groups\Builders\GroupBuilderInterface;
use Gambio\Shop\ProductModifiers\Groups\Builders\InvalidGroupSourceException;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollection;
use Gambio\Shop\ProductModifiers\Groups\GroupInterface;
use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupName;
use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupStatus;
use Gambio\Shop\ProductModifiers\Modifiers\Builders\ModifierBuilderInterface;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\AdditionalInfo;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierName;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierSelectable;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierSelected;
use Gambio\Shop\Properties\ProductModifiers\Database\Readers\PropertyGroupReaderInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\Readers\PropertyModifierReaderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

class PropertyGroupRepository implements PropertyGroupRepositoryInterface
{

    /**
     * @var PropertyGroupReaderInterface
     */
    protected $groupReader;
    /**
     * @var PropertyModifierReaderInterface
     */
    protected $modifierReader;
    /**
     * @var GroupBuilderInterface
     */
    private $groupBuilder;
    /**
     * @var ModifierBuilderInterface
     */
    private $modifierBuilder;
    /**
     * @var PresentationMapperInterface
     */
    private $presentationMapper;
    
    
    /**
     * PropertyGroupRepository constructor.
     *
     * @param PropertyGroupReaderInterface    $groupReader
     * @param PropertyModifierReaderInterface $modifierReader
     * @param GroupBuilderInterface           $groupBuilder
     * @param ModifierBuilderInterface        $modifierBuilder
     * @param PresentationMapperInterface     $mapper
     */
    public function __construct(
        PropertyGroupReaderInterface $groupReader,
        PropertyModifierReaderInterface $modifierReader,
        GroupBuilderInterface $groupBuilder,
        ModifierBuilderInterface $modifierBuilder,
        PresentationMapperInterface $mapper
    ) {

        $this->groupReader        = $groupReader;
        $this->modifierReader     = $modifierReader;
        $this->groupBuilder       = $groupBuilder;
        $this->modifierBuilder    = $modifierBuilder;
        $this->presentationMapper = $mapper;
    }

    public function getGroupsBySellingUnit(SellingUnitId $id): GroupCollection
    {
        $groups    = $this->groupReader->getGroupsBySellingUnit($id);
        $modifiers = $this->modifierReader->getModifierBySellingUnit($id);
        /**
         * @var ModifierDTO $modifier
         */
        foreach ($modifiers as $modifier) {

            $group = $groups->getById($modifier->groupId());
            if ($group) {
                $group->addModifier($modifier);
            }
        }

        $result = new GroupCollection();

        foreach ($groups as $group) {
            $result->addGroup($this->createGroup($group));
        }
        return $result;
    }

    /**
     * @param GroupDTO $dto
     *
     * @return GroupInterface
     * @throws PresentationMapperNotFoundException
     * @throws InvalidGroupSourceException
     */
    protected function createGroup(GroupDTO $dto): GroupInterface
    {
        $this->groupBuilder->withName(new GroupName($dto->name()))
            ->withId($dto->id())
            ->withSource($dto->source())
            ->withStatus(new GroupStatus($dto->isSelectable()))
            ->withType($this->presentationMapper->createPresentationType($dto));
        foreach ($dto->modifiers() as $modifierDTO) {
            $value    = $this->presentationMapper->createPresentationInfo($modifierDTO);
            $modifier = $this->modifierBuilder->withId($modifierDTO->id())
                ->withName(new ModifierName($modifierDTO->name()))
                ->withSelected(new ModifierSelected($modifierDTO->isSelected()))
                ->withSelectable(new ModifierSelectable($modifierDTO->isSelectable()))
                ->withAdditionalInfo(new AdditionalInfo($modifierDTO->pricePrefix(), $modifierDTO->price()))
                ->withValue($value)
                ->build();
            $this->groupBuilder->withModifiers($modifier);
        }

        return $this->groupBuilder->build();
    }
}