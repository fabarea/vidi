<?php

namespace Fab\Vidi\Converter;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;

/**
 * Convert a property name to field.
 */
class Property
{
    /**
     * @var string
     */
    protected static $currentProperty;

    /**
     * @var string
     */
    protected static $currentTable;

    /**
     * @var array
     */
    protected $storage = [];

    /**
     * @param string $propertyName
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function name($propertyName)
    {
        self::$currentProperty = $propertyName;
        self::$currentTable = ''; // reset the table name value.
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * @param string|Content $tableNameOrContentObject
     * @return $this
     */
    public function of($tableNameOrContentObject)
    {
        // Resolve the table name.
        self::$currentTable = $tableNameOrContentObject instanceof Content ?
            $tableNameOrContentObject->getDataType() :
            $tableNameOrContentObject;
        return $this;
    }

    public function toFieldName()
    {
        $propertyName = $this->getPropertyName();
        $tableName = $this->getTableName();

        if (empty($this->storage[$tableName][$propertyName])) {
            if (!array_key_exists($tableName, $this->storage)) {
                $this->storage[$tableName] = [];
            }

            // Default case
            $fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);

            // Special case in case the field name does not follow the conventions "field_name" => "fieldName"
            // There is the chance to make some mapping
            if (!empty($GLOBALS['TCA'][$tableName]['vidi']['mappings'])) {
                $key = array_search($propertyName, $GLOBALS['TCA'][$tableName]['vidi']['mappings']);
                if ($key !== false) {
                    $fieldName = $key;
                }
            }

            $this->storage[$tableName][$propertyName] = $fieldName;
        }

        return $this->storage[$tableName][$propertyName];
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getPropertyName()
    {
        $propertyName = self::$currentProperty;
        if (empty($propertyName)) {
            throw new \RuntimeException('I could not find a field name value.', 1403203290);
        }
        return $propertyName;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getTableName()
    {
        $tableName = self::$currentTable;
        if (empty($tableName)) {
            throw new \RuntimeException('I could not find a table name value.', 1403203291);
        }
        return $tableName;
    }
}
