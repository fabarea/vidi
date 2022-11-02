<?php

namespace Fab\Vidi\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tool\AbstractTool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Fab\Vidi\Exception\InvalidKeyInArrayException;

/**
 * A class to handle TCA ctrl.
 */
class TableService extends AbstractTca
{
    /**
     * @var array
     */
    protected $tca;

    /**
     * @var array
     */
    protected $columnTca;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var array
     */
    protected $instances;

    /**
     * @throws InvalidKeyInArrayException
     * @param string $tableName
     * @return \Fab\Vidi\Tca\TableService
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        if (empty($GLOBALS['TCA'][$this->tableName])) {
            throw new InvalidKeyInArrayException(sprintf('No TCA existence for table "%s"', $this->tableName), 1356945106);
        }
        $this->tca = $GLOBALS['TCA'][$this->tableName]['ctrl'];
        $this->columnTca = $GLOBALS['TCA'][$this->tableName]['columns'];
    }

    /**
     * Tell whether the table has a label field.
     *
     * @throws InvalidKeyInArrayException
     * @return string
     */
    public function hasLabelField()
    {
        return $this->has('label');
    }

    /**
     * Get the label name of table name.
     *
     * @throws InvalidKeyInArrayException
     * @return string
     */
    public function getLabelField()
    {
        $labelField = $this->get('label');
        if (empty($labelField)) {
            throw new InvalidKeyInArrayException(sprintf('No label configured for table "%s"', $this->tableName), 1385586726);
        }
        return $labelField;
    }

    /**
     * Returns the translated label of the table name.
     *
     * @return string
     */
    public function getLabel()
    {
        $label = '';
        try {
            $label = LocalizationUtility::translate($this->getLabelField(), '');
        } catch (\InvalidArgumentException $e) {
        }
        if (empty($label)) {
            $label = $this->getLabelField();
        }
        return $label;
    }

    /**
     * Returns the title of the table.
     *
     * @return string
     */
    public function getTitle()
    {
        $title = '';
        try {
            $title = LocalizationUtility::translate((string)$this->get('title'), '');
        } catch (\InvalidArgumentException $e) {
        }
        if (empty($title)) {
            $title = $this->get('title');
        }
        return $title;
    }

    /**
     * Return the "disabled" field.
     *
     * @throws InvalidKeyInArrayException
     * @return string|null
     */
    public function getHiddenField()
    {
        $hiddenField = null;
        $enableColumns = $this->get('enablecolumns');
        if (is_array($enableColumns) && !empty($enableColumns['disabled'])) {
            $hiddenField = $enableColumns['disabled'];
        }
        return $hiddenField;
    }

    /**
     * Return the "starttime" field.
     *
     * @throws InvalidKeyInArrayException
     * @return string|null
     */
    public function getStartTimeField()
    {
        $startTimeField = null;
        $enableColumns = $this->get('enablecolumns');
        if (is_array($enableColumns) && !empty($enableColumns['starttime'])) {
            $startTimeField = $enableColumns['starttime'];
        }
        return $startTimeField;
    }

    /**
     * Return the "endtime" field.
     *
     * @throws InvalidKeyInArrayException
     * @return string|null
     */
    public function getEndTimeField()
    {
        $endTimeField = null;
        $enableColumns = $this->get('enablecolumns');
        if (is_array($enableColumns) && !empty($enableColumns['endtime'])) {
            $endTimeField = $enableColumns['endtime'];
        }
        return $endTimeField;
    }

    /**
     * Tells whether the table is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return isset($this->tca['hideTable']) ? $this->tca['hideTable'] : false;
    }

    /**
     * Tells whether the table is not hidden.
     *
     * @return bool
     */
    public function isNotHidden()
    {
        return !$this->isHidden();
    }

    /**
     * Get the "deleted" field for the table.
     *
     * @return string|null
     */
    public function getDeletedField()
    {
        return $this->get('delete');
    }

    /**
     * Get the modification time stamp field.
     *
     * @return string|null
     */
    public function getTimeModificationField()
    {
        return $this->get('tstamp');
    }

    /**
     * Get the creation time stamp field.
     *
     * @return string|null
     */
    public function getTimeCreationField()
    {
        return $this->get('crdate');
    }

    /**
     * Get the language field for the table.
     *
     * @return string|null
     */
    public function getLanguageField()
    {
        return $this->get('languageField');
    }

    /**
     * Get the field which points to the parent.
     *
     * @return string|null
     */
    public function getLanguageParentField()
    {
        return $this->get('transOrigPointerField');
    }

    /**
     * Returns the default order in the form of a SQL segment.
     *
     * @return string|null
     */
    public function getDefaultOrderSql()
    {
        // "sortby" typically has "sorting" as value.
        $order = $this->get('sortby') ? $this->get('sortby') . ' ASC' : $this->get('default_sortby');
        return $order;
    }

    /**
     * Returns the parsed default orderings.
     * Returns array looks like array('title' => 'ASC');
     *
     * @return array
     */
    public function getDefaultOrderings()
    {
        // first clean up the sql segment
        $defaultOrder = str_replace('ORDER BY', '', $this->getDefaultOrderSql());
        $defaultOrderParts = GeneralUtility::trimExplode(',', $defaultOrder, true);

        $orderings = [];
        foreach ($defaultOrderParts as $defaultOrderPart) {
            $parts = GeneralUtility::trimExplode(' ', $defaultOrderPart);
            if (empty($parts[1])) {
                $parts[1] = QueryInterface::ORDER_DESCENDING;
            }
            $orderings[$parts[0]] = $parts[1];
        }

        return $orderings;
    }

    /**
     * Returns the searchable fields.
     *
     * @return string|null
     */
    public function getSearchFields()
    {
        return $this->get('searchFields');
    }

    /**
     * Returns an array containing the field names.
     *
     * @return array
     */
    public function getFields()
    {
        return array_keys($this->columnTca);
    }

    /**
     * Returns an array containing the fields and their configuration.
     *
     * @return array
     */
    public function getFieldsAndConfiguration()
    {
        return $this->columnTca;
    }

    /**
     * Tell whether we have a field "sorting".
     *
     * @return bool
     */
    public function hasSortableField()
    {
        return $this->has('sortby');
    }

    /**
     * Tell whether the field exists or not.
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasField($fieldName)
    {
        if ($this->isComposite($fieldName)) {
            $parts = explode('.', $fieldName);
            [$strippedFieldPath, $possibleTableName] = $parts;
            $hasField = isset($this->columnTca[$strippedFieldPath], $GLOBALS['TCA'][$possibleTableName]);

            // Continue checking that the $strippedFieldName is of type "group"
            if (isset($GLOBALS['TCA'][$this->tableName]['columns'][$strippedFieldPath]) && count($parts) > 2) {
                $hasField = Tca::table($this->tableName)->field($strippedFieldPath)->isGroup(); // Group
            } elseif (isset($this->columnTca[$strippedFieldPath]['config']['readOnly']) && (bool)$this->columnTca[$strippedFieldPath]['config']['readOnly']) {
                $hasField = false; // handle case metadata.fe_groups where "fe_groups" is a tableName.
            }
        } else {
            $hasField = isset($this->columnTca[$fieldName]) || in_array($fieldName, Tca::getSystemFields(), true);
        }
        return $hasField;
    }

    /**
     * Tell whether the field name contains a path, e.g. metadata.title
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isComposite($fieldName)
    {
        return strpos($fieldName, '.') > 0;
    }

    /**
     * Tells whether the $key exists.
     *
     * @param string $key
     * @return string
     */
    public function has($key)
    {
        return isset($this->tca[$key]);
    }

    /**
     * Tells whether the table name has "workspace" support.
     *
     * @return string
     */
    public function hasWorkspaceSupport()
    {
        return isset($this->tca['versioningWS']);
    }

    /**
     * Tells whether the table name has "language" support.
     *
     * @return string
     */
    public function hasLanguageSupport()
    {
        return isset($this->tca['languageField']);
    }

    /**
     * Return configuration value given a key.
     *
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->tca[$key] : null;
    }

    /**
     * @return array
     */
    public function getTca()
    {
        return $this->tca;
    }

    /**
     * Tell whether the current BE User has access to this field.
     *
     * @return bool
     */
    public function hasAccess()
    {
        $hasAccess = true;
        if (AbstractTool::isBackend()) {
            $hasAccess = $this->getBackendUser()->check('tables_modify', $this->tableName);
        }
        return $hasAccess;
    }

    /**
     * @param string $fieldName
     * @throws \Exception
     * @return FieldService
     */
    public function field($fieldName)
    {
        // In case field contains items.tx_table for field type "group"
        $compositeField = '';
        if (strpos($fieldName, '.') !== false) {
            $compositeField = $fieldName;
            $fieldParts = explode('.', $compositeField, 2);
            $fieldName = $fieldParts[0];

            // Special when field has been instantiated without the field name and path.
            if (!empty($this->instances[$fieldName])) {
                /** @var FieldService $field */
                $field = $this->instances[$fieldName];
                $field->setCompositeField($compositeField);
            }
        }

        // True for system fields such as uid, pid that don't necessarily have a TCA.
        if (empty($this->columnTca[$fieldName]) && in_array($fieldName, Tca::getSystemFields())) {
            $this->columnTca[$fieldName] = [];
        } elseif (empty($this->columnTca[$fieldName])) {
            $message = sprintf(
                'Does the field really exist? No TCA entry found for field "%s" for table "%s"',
                $fieldName,
                $this->tableName
            );
            throw new \Exception($message, 1385554481);
        }


        if (empty($this->instances[$fieldName])) {
            $instance = GeneralUtility::makeInstance(
                'Fab\Vidi\Tca\FieldService',
                $fieldName,
                $this->columnTca[$fieldName],
                $this->tableName,
                $compositeField
            );

            $this->instances[$fieldName] = $instance;
        }
        return $this->instances[$fieldName];
    }
}
