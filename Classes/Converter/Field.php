<?php
namespace Fab\Vidi\Converter;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;

/**
 * Convert a field name to property name.
 */
class Field implements SingletonInterface
{

    /**
     * @var string
     */
    static protected $currentField;

    /**
     * @var string
     */
    static protected $currentTable;

    /**
     * @var array
     */
    protected $storage = [];

    /**
     * @param string $fieldName
     * @return $this
     * @throws \InvalidArgumentException
     */
    static public function name($fieldName)
    {
        self::$currentField = $fieldName;
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

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function toPropertyName()
    {

        $fieldName = $this->getFieldName();
        $tableName = $this->getTableName();

        if (empty($this->storage[$tableName][$fieldName])) {
            if ($this->storage[$tableName]) {
                $this->storage[$tableName] = [];
            }

            // Special case when the field name does not follow the conventions "field_name" => "fieldName".
            // Rely on mapping for those cases.
            if (!empty($GLOBALS['TCA'][$tableName]['vidi']['mappings'][$fieldName])) {
                $propertyName = $GLOBALS['TCA'][$tableName]['vidi']['mappings'][$fieldName];
            } else {
                $propertyName = GeneralUtility::underscoredToLowerCamelCase($fieldName);
            }

            $this->storage[$tableName][$fieldName] = $propertyName;
        }

        return $this->storage[$tableName][$fieldName];
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getFieldName()
    {
        $fieldName = self::$currentField;
        if (empty($fieldName)) {
            throw new \RuntimeException('I could not find a field name value.', 1403203290);
        }
        return $fieldName;
    }

    /**
     * @return string
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
