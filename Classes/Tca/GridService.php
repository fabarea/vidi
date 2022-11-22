<?php

namespace Fab\Vidi\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Resolver\FieldPathResolver;
use Fab\Vidi\Grid\ColumnRendererInterface;
use Fab\Vidi\Module\ConfigurablePart;
use Fab\Vidi\Module\ModulePreferences;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Exception\InvalidKeyInArrayException;
use Fab\Vidi\Facet\StandardFacet;
use Fab\Vidi\Facet\FacetInterface;

/**
 * A class to handle TCA grid configuration
 */
class GridService extends AbstractTca
{
    /**
     * @var array
     */
    protected $tca;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * All fields available in the Grid.
     *
     * @var array
     */
    protected $fields;

    /**
     * All fields regardless whether they have been excluded or not.
     *
     * @var array
     */
    protected $allFields;

    /**
     * @var array
     */
    protected $instances;

    /**
     * @var array
     */
    protected $facets;

    /**
     * __construct
     *
     * @param string $tableName
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;

        if (empty($GLOBALS['TCA'][$this->tableName])) {
            throw new InvalidKeyInArrayException('No TCA existence for table name: ' . $this->tableName, 1356945108);
        }

        $this->tca = $GLOBALS['TCA'][$this->tableName]['grid'] ?? [];
    }

    /**
     * Returns an array containing column names.
     *
     * @return array
     */
    public function getFieldNames(): array
    {
        $fields = $this->getFields();
        return array_keys($fields) ?: [];
    }

    /**
     * Returns an array containing column names.
     *
     * @return array
     */
    public function getAllFieldNames(): array
    {
        $allFields = $this->getAllFields();
        return array_keys($allFields);
    }

    /**
     * Get the label key.
     *
     * @param string $fieldNameAndPath
     * @return string
     */
    public function getLabelKey($fieldNameAndPath): string
    {
        $field = $this->getField($fieldNameAndPath);

        // First option is to get the label from the Grid TCA.
        $rawLabel = '';
        if (isset($field['label'])) {
            $rawLabel = $field['label'];
        }

        // Second option is to fetch the label from the Column Renderer object.
        if (!$rawLabel && $this->hasRenderers($fieldNameAndPath)) {
            $renderers = $this->getRenderers($fieldNameAndPath);
            /** @var $renderer ColumnRendererInterface */
            foreach ($renderers as $renderer) {
                if (isset($renderer['label'])) {
                    $rawLabel = $renderer['label'];
                    break;
                }
            }
        }
        return $rawLabel;
    }

    /**
     * Get the translation of a label given a column name.
     *
     * @param string $fieldNameAndPath
     * @return string
     */
    public function getLabel($fieldNameAndPath)
    {
        $label = '';
        if ($this->hasLabel($fieldNameAndPath)) {
            $labelKey = $this->getLabelKey($fieldNameAndPath);
            try {
                $label = $this->getLanguageService()->sL($labelKey);
            } catch (\InvalidArgumentException $e) {
            }
            if (empty($label)) {
                $label = $labelKey;
            }
        } else {
            // Important to notice the label can contains a path, e.g. metadata.categories and must be resolved.
            $dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $this->tableName);
            $fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $this->tableName);
            $table = Tca::table($dataType);

            if ($table->hasField($fieldName) && $table->field($fieldName)->hasLabel()) {
                $label = $table->field($fieldName)->getLabel();
            }
        }

        return $label;
    }

    /**
     * Returns the field name given its position.
     *
     * @param string $position the position of the field in the grid
     * @return string
     */
    public function getFieldNameByPosition($position): string
    {
        $fields = array_keys($this->getFields());
        if (empty($fields[$position])) {
            throw new InvalidKeyInArrayException('No field exist for position: ' . $position, 1356945119);
        }

        return (string)$fields[$position];
    }

    /**
     * Returns a field name.
     *
     * @param string $fieldName
     * @return array
     */
    public function getField($fieldName): array
    {
        $fields = $this->getFields();
        return $fields[$fieldName] ?: [];
    }

    /**
     * Returns an array containing column names for the Grid.
     *
     * @return array
     */
    public function getFields(): array
    {
        // Cache this operation since it can take some time.
        if ($this->fields === null) {
            // Fetch all available fields first.
            $fields = $this->getAllFields();

            if ($this->isBackendMode()) {
                // Then remove the not allowed.
                $fields = $this->filterByIncludedFields($fields);
                $fields = $this->filterByBackendUser($fields);
                $fields = $this->filterByExcludedFields($fields);
            }

            $this->fields = $fields;
        }

        return $this->fields;
    }

    /**
     * Remove fields according to Grid configuration.
     *
     * @param $fields
     * @return array
     */
    protected function filterByIncludedFields($fields): array
    {
        $filteredFields = $fields;
        $includedFields = $this->getIncludedFields();
        if (count($includedFields) > 0) {
            $filteredFields = [];
            foreach ($fields as $fieldNameAndPath => $configuration) {
                if (in_array($fieldNameAndPath, $includedFields, true) || !Tca::table($this->tableName)->hasField($fieldNameAndPath)) {
                    $filteredFields[$fieldNameAndPath] = $configuration;
                }
            }
        }
        return $filteredFields;
    }

    /**
     * Remove fields according to BE User permission.
     *
     * @param $fields
     * @return array
     */
    protected function filterByBackendUser($fields): array
    {
        if (!$this->getBackendUser()->isAdmin()) {
            foreach ($fields as $fieldName => $field) {
                if (Tca::table($this->tableName)->hasField($fieldName) && !Tca::table($this->tableName)->field($fieldName)->hasAccess()) {
                    unset($fields[$fieldName]);
                }
            }
        }
        return $fields;
    }

    /**
     * Remove fields according to Grid configuration.
     *
     * @param $fields
     * @return array
     */
    protected function filterByExcludedFields($fields): array
    {
        // Unset excluded fields.
        foreach ($this->getExcludedFields() as $excludedField) {
            if (isset($fields[$excludedField])) {
                unset($fields[$excludedField]);
            }
        }

        return $fields;
    }

    /**
     * Returns an array containing column names for the Grid.
     *
     * @return array
     */
    public function getAllFields(): array
    {
        // Cache this operation since it can take some time.
        if ($this->allFields === null) {
            $fields = isset($this->tca['columns']) && is_array($this->tca['columns']) ? $this->tca['columns'] : [];
            $gridFieldNames = array_keys($fields);

            // Fetch all fields of the TCA and merge it back to the fields configured for Grid.
            $tableFieldNames = Tca::table($this->tableName)->getFields();

            // Just remove system fields from the Grid.
            foreach ($tableFieldNames as $key => $fieldName) {
                if (in_array($fieldName, Tca::getSystemFields())) {
                    unset($tableFieldNames[$key]);
                }
            }

            $additionalFields = array_diff($tableFieldNames, $gridFieldNames);

            if (!empty($additionalFields)) {
                // Pop out last element of the key
                // Idea is to place new un-configured columns in between. By default, they will be hidden.
                end($fields);
                $lastColumnKey = key($fields);
                $lastColumn = array_pop($fields);

                // Feed up the grid fields with un configured elements
                foreach ($additionalFields as $additionalField) {
                    $fields[$additionalField] = array(
                        'visible' => false
                    );

                    // Try to guess the format of the field.
                    $fieldType = Tca::table($this->tableName)->field($additionalField)->getType();
                    if ($fieldType === FieldType::DATE) {
                        $fields[$additionalField]['format'] = 'Fab\Vidi\Formatter\Date';
                    } elseif ($fieldType === FieldType::DATETIME) {
                        $fields[$additionalField]['format'] = 'Fab\Vidi\Formatter\Datetime';
                    }
                }
                $fields[$lastColumnKey] = $lastColumn;
            }

            $this->allFields = $fields;
        }

        return $this->allFields;
    }

    /**
     * Tell whether the field exists in the grid or not.
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasField($fieldName): bool
    {
        $fields = $this->getFields();
        return isset($fields[$fieldName]);
    }

    /**
     * Tell whether the facet exists in the grid or not.
     *
     * @param string $facetName
     * @return bool
     */
    public function hasFacet($facetName): bool
    {
        $facets = $this->getFacets();
        return isset($facets[$facetName]);
    }

    /**
     * Returns an array containing facets fields.
     *
     * @return FacetInterface[]
     */
    public function getFacets(): array
    {
        if ($this->facets === null) {
            $this->facets = [];

            if (is_array($this->tca['facets'])) {
                foreach ($this->tca['facets'] as $key => $facetNameOrArray) {
                    if (is_array($facetNameOrArray)) {
                        $name = $facetNameOrArray['name'] ?? '';
                        $class = $facetNameOrArray['class'] ?? $key;

                        $label = isset($facetNameOrArray['label'])
                            ? $this->getLanguageService()->sL($facetNameOrArray['label'])
                            : '';

                        $suggestions = $facetNameOrArray['suggestions'] ?? [];
                        $configuration = $facetNameOrArray['configuration'] ?? [];

                        /** @var FacetInterface $facetObject */
                        $facetObject = GeneralUtility::makeInstance($class, $name, $label, $suggestions, $configuration);
                        $this->facets[$facetObject->getName()] = $facetObject;
                    } else {
                        $this->facets[$facetNameOrArray] = $this->instantiateStandardFacet($facetNameOrArray);
                    }
                }
            }
        }
        return $this->facets;
    }

    /**
     * Returns the "sortable" value of the column.
     *
     * @param string $fieldName
     * @return int|string
     */
    public function isSortable($fieldName)
    {
        $defaultValue = true;
        $hasSortableField = Tca::table($this->tableName)->hasSortableField();
        if ($hasSortableField) {
            $isSortable = false;
        } else {
            $isSortable = $this->get($fieldName, 'sortable', $defaultValue);
        }
        return $isSortable;
    }

    /**
     * Returns the "canBeHidden" value of the column.
     *
     * @param string $fieldName
     * @return bool
     */
    public function canBeHidden($fieldName): bool
    {
        $defaultValue = true;
        return $this->get($fieldName, 'canBeHidden', $defaultValue);
    }

    /**
     * Returns the "width" value of the column.
     *
     * @param string $fieldName
     * @return int|string
     */
    public function getWidth($fieldName)
    {
        $defaultValue = 'auto';
        return $this->get($fieldName, 'width', $defaultValue);
    }

    /**
     * Returns the "visible" value of the column.
     *
     * @param string $fieldName
     * @return bool
     */
    public function isVisible($fieldName): bool
    {
        $defaultValue = true;
        return $this->get($fieldName, 'visible', $defaultValue);
    }

    /**
     * Returns the "editable" value of the column.
     *
     * @param string $columnName
     * @return bool
     */
    public function isEditable($columnName): bool
    {
        $defaultValue = false;
        return $this->get($columnName, 'editable', $defaultValue);
    }

    /**
     * Returns the "localized" value of the column.
     *
     * @param string $columnName
     * @return bool
     */
    public function isLocalized($columnName): bool
    {
        $defaultValue = true;
        return $this->get($columnName, 'localized', $defaultValue);
    }

    /**
     *
     * Returns the "html" value of the column.
     *
     * @param string $fieldName
     * @return string
     */
    public function getHeader($fieldName): string
    {
        $defaultValue = '';
        return $this->get($fieldName, 'html', $defaultValue);
    }

    /**
     * Fetch a possible from a Grid Renderer. If no value is found, returns null
     *
     * @param string $fieldName
     * @param string $key
     * @param mixed $defaultValue
     * @return null|mixed
     */
    public function get($fieldName, $key, $defaultValue = null)
    {
        $value = $defaultValue;

        $field = $this->getField($fieldName);
        if (isset($field[$key])) {
            $value = $field[$key];
        } elseif ($this->hasRenderers($fieldName)) {
            $renderers = $this->getRenderers($fieldName);
            foreach ($renderers as $rendererConfiguration) {
                if (isset($rendererConfiguration[$key])) {
                    $value = $rendererConfiguration[$key];
                }
            }
        }
        return $value;
    }

    /**
     * Returns whether the column has a renderer.
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasRenderers($fieldName): bool
    {
        $field = $this->getField($fieldName);
        return empty($field['renderer']) && empty($field['renderers']) ? false : true;
    }

    /**
     * Returns a renderer.
     *
     * @param string $fieldName
     * @return array
     */
    public function getRenderers($fieldName): array
    {
        $field = $this->getField($fieldName);
        $renderers = [];
        if (!empty($field['renderer'])) {
            $renderers = $this->convertRendererToArray($field['renderer'], $field);
        } elseif (!empty($field['renderers']) && is_array($field['renderers'])) {
            foreach ($field['renderers'] as $renderer) {
                $rendererNameAndConfiguration = $this->convertRendererToArray($renderer, $field);
                $renderers = array_merge($renderers, $rendererNameAndConfiguration);
            }
        }

        return $renderers;
    }

    /**
     * @param string $renderer
     * @return array
     */
    protected function convertRendererToArray($renderer, array $field): array
    {
        $result = [];
        if (is_string($renderer)) {
            $configuration = empty($field['rendererConfiguration'])
                ? []
                : $field['rendererConfiguration'];

            /** @var ColumnRendererInterface $rendererObject */
            $rendererObject = GeneralUtility::makeInstance($renderer);

            $result[$renderer] = array_merge($rendererObject->getConfiguration(), $configuration);
        // TODO: throw alert message because this is not compatible anymore as of TYPO3 8.7.7
        } elseif ($renderer instanceof ColumnRendererInterface) {
            /** @var ColumnRendererInterface $renderer */
            $result[get_class($renderer)] = $renderer->getConfiguration();
        }
        return $result;
    }

    /**
     * Returns the class names applied to a cell
     *
     * @param string $fieldName
     * @return bool
     */
    public function getClass($fieldName): bool
    {
        $field = $this->getField($fieldName);
        return isset($field['class']) ? $field['class'] : '';
    }

    /**
     * Returns whether the column has a label.
     *
     * @param string $fieldNameAndPath
     * @return bool
     */
    public function hasLabel($fieldNameAndPath): bool
    {
        $field = $this->getField($fieldNameAndPath);

        $hasLabel = empty($field['label']) ? false : true;

        if (!$hasLabel && $this->hasRenderers($fieldNameAndPath)) {
            $renderers = $this->getRenderers($fieldNameAndPath);
            /** @var $renderer ColumnRendererInterface */
            foreach ($renderers as $renderer) {
                if (isset($renderer['label'])) {
                    $hasLabel = true;
                    break;
                }
            }
        }
        return $hasLabel;
    }

    /**
     * @return array
     */
    public function getTca(): array
    {
        return $this->tca;
    }

    /**
     * @return array
     */
    public function getIncludedFields(): array
    {
        return empty($this->tca['included_fields']) ? [] : GeneralUtility::trimExplode(',', $this->tca['included_fields'], true);
    }

    /**
     * Return excluded fields from configuration + preferences.
     *
     * @return array
     */
    public function getExcludedFields(): array
    {
        $configurationFields = $this->getExcludedFieldsFromConfiguration();
        $preferencesFields = $this->getExcludedFieldsFromPreferences();

        return array_merge($configurationFields, $preferencesFields);
    }

    /**
     * Fetch excluded fields from configuration.
     *
     * @return array
     */
    protected function getExcludedFieldsFromConfiguration(): array
    {
        $excludedFields = [];
        if (!empty($this->tca['excluded_fields'])) {
            $excludedFields = GeneralUtility::trimExplode(',', $this->tca['excluded_fields'], true);
        } elseif (!empty($this->tca['export']['excluded_fields'])) { // only for export for legacy reason.
            $excludedFields = GeneralUtility::trimExplode(',', $this->tca['export']['excluded_fields'], true);
        }
        return $excludedFields;
    }

    /**
     * Fetch excluded fields from preferences.
     *
     * @return array
     */
    protected function getExcludedFieldsFromPreferences(): array
    {
        $excludedFields = $this->getModulePreferences()->get(ConfigurablePart::EXCLUDED_FIELDS, $this->tableName);
        return is_array($excludedFields) ? $excludedFields : [];
    }

    /**
     * @return bool
     */
    public function areFilesIncludedInExport(): bool
    {
        $isIncluded = true;

        if (isset($this->tca['export']['include_files'])) {
            $isIncluded = (bool)$this->tca['export']['include_files'];
        }
        return $isIncluded;
    }

    /**
     * Returns a "facet" service instance.
     *
     * @param string|FacetInterface $facetName
     * @return StandardFacet
     */
    protected function instantiateStandardFacet($facetName): StandardFacet
    {
        $label = $this->getLabel($facetName);

        /** @var StandardFacet $facetName */
        $facet = GeneralUtility::makeInstance(StandardFacet::class, $facetName, $label);

        if (!$facet instanceof StandardFacet) {
            throw new \RuntimeException('I could not instantiate a facet for facet name "' . $facetName . '""', 1445856345);
        }
        return $facet;
    }

    /**
     * Returns a "facet" service instance.
     *
     * @param string|FacetInterface $facetName
     * @return FacetInterface
     */
    public function facet($facetName = ''): FacetInterface
    {
        $facets = $this->getFacets();
        return $facets[$facetName];
    }

    /**
     * @return FieldPathResolver|object
     */
    protected function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance(FieldPathResolver::class);
    }

    /**
     * @return ModulePreferences|object
     */
    protected function getModulePreferences()
    {
        return GeneralUtility::makeInstance(ModulePreferences::class);
    }

    /**
     * @return LanguageService|object
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class);
    }
}
