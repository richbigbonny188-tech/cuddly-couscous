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

namespace Gambio\Shop\Attributes\Representation\SelectionHtml\Repository\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Gambio\Shop\Attributes\Representation\SelectionHtml\Exceptions\InvalidValueIdsSpecifiedException;
use Gambio\Shop\Attributes\Representation\SelectionHtml\Repository\DTO\AttributeNameAndValueDTO;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;

/**
 * Class Reader
 * @package Gambio\Shop\Attributes\Representation\SelectionHtml\Repository\Readers
 */
class Reader implements ReaderInterface
{
    /**
     * @var Connection
     */
    protected $connection;
    
    
    /**
     * Reader constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function selectionData(ModifierIdentifierInterface $identifier, LanguageId $languageId): AttributeNameAndValueDTO
    {
        $builder        = $this->connection->createQueryBuilder();
        $optionIdResult = $builder->select('options_id')
            ->distinct()
            ->from('products_attributes')
            ->where('options_values_id=' . $identifier->value())
            ->execute();
        
        if ($optionIdResult->rowCount() === 0) {
            
            throw InvalidValueIdsSpecifiedException::forValueId($identifier->value());
        }
        
        $optionId = (int)$optionIdResult->fetch(FetchMode::ASSOCIATIVE)['options_id'];
    
        return new AttributeNameAndValueDTO($this->optionName($optionId, $languageId),
                                            $this->optionValueName($identifier->value(), $languageId),
                                            $identifier);
    }
    
    
    /**
     * @param int        $optionId
     *
     * @param LanguageId $languageId
     *
     * @return string
     * @throws InvalidValueIdsSpecifiedException
     */
    protected function optionName(int $optionId, LanguageId $languageId): string
    {
        $builder          = $this->connection->createQueryBuilder();
        $optionNameResult = $builder->select('products_options_name')
            ->from('products_options')
            ->where('products_options_id=' . $optionId)
            ->andWhere('language_id=' . $languageId->value())
            ->execute();
        
        if ($optionNameResult->rowCount() === 0) {
            
            throw InvalidValueIdsSpecifiedException::noOptionNameInLanguage($optionId, $languageId);
        }
        
        return $optionNameResult->fetch(FetchMode::ASSOCIATIVE)['products_options_name'];
    }
    
    
    /**
     * @param int        $optionValueId
     * @param LanguageId $languageId
     *
     * @return string
     * @throws InvalidValueIdsSpecifiedException
     */
    protected function optionValueName(int $optionValueId, LanguageId $languageId): string
    {
        $builder = $this->connection->createQueryBuilder();
        $valueNameResult = $builder->select('products_options_values_name')
            ->from('products_options_values')
            ->where('products_options_values_id=' . $optionValueId)
            ->andWhere('language_id=' . $languageId->value())
            ->execute();
        
        if ($valueNameResult->rowCount() === 0) {
            
            throw InvalidValueIdsSpecifiedException::noOptionValueNameInLanguage($optionValueId, $languageId);
        }
        
        return $valueNameResult->fetch(FetchMode::ASSOCIATIVE)['products_options_values_name'];
    }
}