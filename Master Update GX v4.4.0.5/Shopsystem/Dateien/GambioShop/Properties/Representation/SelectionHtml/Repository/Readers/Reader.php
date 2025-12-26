<?php
/**
 * Reader.php 2021-01-26
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);

namespace Gambio\Shop\Properties\Representation\SelectionHtml\Repository\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\Properties\Representation\SelectionHtml\Exceptions\InvalidValueIdsSpecifiedException;
use Gambio\Shop\Properties\Representation\SelectionHtml\Repository\DTO\PropertyNameAndValueDto;

/**
 * Class Reader
 * @package Gambio\Shop\Properties\Representation\SelectionHtml\Repository\Readers
 */
class Reader implements ReaderInterface
{
    /**
     * @var Connection
     */
    protected $connection;
    
    
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function selectionData(
        ModifierIdentifierInterface $identifier,
        LanguageId $languageId
    ): PropertyNameAndValueDto {
    
        $builder          = $this->connection->createQueryBuilder();
        $propertyIdResult = $builder->select('properties_id')
            ->from('properties_values')
            ->where('properties_values_id=' . $identifier->value())
            ->execute();
        
        if ($propertyIdResult->rowCount() === 0) {
            
            throw InvalidValueIdsSpecifiedException::forValueId($identifier->value());
        }
        
        $propertyId = (int)$propertyIdResult->fetch(FetchMode::ASSOCIATIVE)['properties_id'];
    
        return new PropertyNameAndValueDto($this->propertyName($propertyId, $languageId),
                                           $this->propertyValueName($identifier->value(), $languageId));
    }
    
    
    /**
     * @param int        $propertyValueId
     * @param LanguageId $languageId
     *
     * @return string
     * @throws InvalidValueIdsSpecifiedException
     */
    protected function propertyValueName(int $propertyValueId, LanguageId $languageId): string
    {
        $builder         = $this->connection->createQueryBuilder();
        $valueNameResult = $builder->select('values_name')
            ->from('properties_values_description')
            ->where('properties_values_id=' . $propertyValueId)
            ->andWhere('language_id=' . $languageId->value())
            ->execute();
        
        if ($valueNameResult->rowCount() === 0) {
            
            throw InvalidValueIdsSpecifiedException::noPropertyValueNameInLanguage($propertyValueId, $languageId);
        }
        
        return $valueNameResult->fetch(FetchMode::ASSOCIATIVE)['values_name'];
    }
    
    /**
     * @param int        $propertiesId
     * @param LanguageId $languageId
     *
     * @return string
     * @throws InvalidValueIdsSpecifiedException
     */
    protected function propertyName(int $propertiesId, LanguageId $languageId): string
    {
        $builder = $this->connection->createQueryBuilder();
        $propertyNameResult = $builder->select('properties_name')
            ->from('properties_description')
            ->where('properties_id=' . $propertiesId)
            ->andWhere('language_id=' . $languageId->value())
            ->execute();
        
        if ($propertyNameResult->rowCount() === 0) {
            
            throw InvalidValueIdsSpecifiedException::noPropertyNameInLanguage($propertiesId, $languageId);
        }
        
        return $propertyNameResult->fetch(FetchMode::ASSOCIATIVE)['properties_name'];
    }
}