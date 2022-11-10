<?php

namespace Fab\Vidi\Domain\Model;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Exception\NotExistingClassException;
use Fab\Vidi\Tca\TableService;
use Fab\Vidi\Utility\Typo3Mode;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;
use Fab\Vidi\Domain\Repository\ContentRepository;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use Fab\Vidi\Tca\FieldType;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use Fab\Vidi\Domain\Repository\ContentRepositoryFactory;
use Fab\Vidi\Converter\Field;
use Fab\Vidi\Converter\Property;
use Fab\Vidi\Service\FileReferenceService;
use Fab\Vidi\Tca\Tca;

/**
 * Content representation.
 */
class Content implements \ArrayAccess
{
    /**
     * @var int
     */
    protected $uid;

    /**
     * @var string
     */
    protected $dataType;

    /**
     * Constructor for a Content object.
     *
     * @param string $dataType will basically correspond to a table name, e.g fe_users, tt_content, ...
     * @param array $contentData
     * @return \Fab\Vidi\Domain\Model\Content
     * @throws \InvalidArgumentException
     * @throws NotExistingClassException
     */
    public function __construct($dataType, array $contentData = array())
    {
        $this->dataType = $dataType;
        $this->uid = empty($contentData['uid']) ? null : (int)$contentData['uid'];

        /** @var TableService $table */
        $table = Tca::table($dataType);

        // Initialize the array containing the allowed fields to be filled-in.
        $fields = array('pid');

        // If a creation time stamp has been defined for this data type.
        if ($table->getTimeCreationField()) {
            $fields[] = $table->getTimeCreationField();
        }

        // If an update time stamp has been defined for this data type.
        if ($table->getTimeModificationField()) {
            $fields[] = $table->getTimeModificationField();
        }

        // Merge the other fields allowed for this data type.
        $fields = array_merge($fields, $table->getFields());

        // Fetch excluded fields from the grid.
        if ($this->isBackendMode()) {
            $fields = $this->filterForConfiguration($fields);
            $fields = $this->filterForBackendUser($fields);
        }

        // Get column to be displayed
        foreach ($fields as $fieldName) {
            if (array_key_exists($fieldName, $contentData)) {
                $propertyName = Field::name($fieldName)->of($dataType)->toPropertyName();
                $this->$propertyName = $contentData[$fieldName];
            }
        }
    }

    /**
     * Dispatches magic methods (findBy[Property]())
     *
     * @param string $methodName The name of the magic method
     * @param string $arguments The arguments of the magic method
     * @throws UnsupportedMethodException
     * @return mixed
     * @api
     */
    public function __call($methodName, $arguments)
    {
        $value = null;
        if (substr($methodName, 0, 3) === 'get' && strlen($methodName) > 4) {
            $propertyName = strtolower(substr(substr($methodName, 3), 0, 1)) . substr(substr($methodName, 3), 1);

            $fieldName = Property::name($propertyName)->of($this)->toFieldName();
            $field = Tca::table($this->dataType)->field($fieldName);

            $value = $this->$propertyName;

            // true means it is a relation and it is not yet resolved.
            if ($this->hasRelation($propertyName) && is_scalar($this->$propertyName)) {
                $value = $this->resolveRelation($propertyName);
            } elseif ($field->getType() === FieldType::RADIO || $field->getType() === FieldType::SELECT) {
                // Attempt to convert the value into a label for radio and select fields.
                $label = Tca::table($this->getDataType())->field($fieldName)->getLabelForItem($value);
                if ($label) {
                    $value = $label;
                }
            }
        } elseif (substr($methodName, 0, 3) === 'set' && strlen($methodName) > 4 && isset($arguments[0])) {
            $propertyName = strtolower(substr(substr($methodName, 3), 0, 1)) . substr(substr($methodName, 3), 1);
            $this->$propertyName = $arguments[0];
        }
        return $value;
    }

    /**
     * Tell whether the property has a relation.
     *
     * @param string $propertyName
     * @return bool
     */
    protected function hasRelation($propertyName): bool
    {
        $fieldName = Property::name($propertyName)->of($this)->toFieldName();
        return Tca::table($this->dataType)->field($fieldName)->hasRelation();
    }

    /**
     * Try to "resolve" the property whether it has a relation.
     * If the property has not relation it simply returns the same value.
     *
     * @throws \RuntimeException
     * @param string $propertyName
     * @return mixed
     */
    protected function resolveRelation($propertyName)
    {
        // Convert property name to field name and get the foreign data type.
        $fieldName = Property::name($propertyName)->of($this)->toFieldName();
        $foreignDataType = Tca::table($this->dataType)->field($fieldName)->relationDataType();

        // Get the foreign repository instance form the factory
        /** @var ContentRepository $foreignContentRepository */
        $foreignContentRepository = ContentRepositoryFactory::getInstance($foreignDataType, $fieldName);

        if (Tca::table($this->dataType)->field($fieldName)->hasRelationWithCommaSeparatedValues()) {
            // Fetch values from repository
            $values = GeneralUtility::trimExplode(',', $this->$propertyName);
            $this->$propertyName = $foreignContentRepository->findIn('uid', $values);
        } elseif (Tca::table($this->dataType)->field($fieldName)->hasMany()) {
            // Include relation many-to-many and one-to-many
            // Tca::table($this->dataType)->field($fieldName)->hasRelationOneToMany()
            // Tca::table($this->dataType)->field($fieldName)->hasRelationManyToMany()

            $foreignFieldName = Tca::table($this->dataType)->field($fieldName)->getForeignField();
            if (empty($foreignFieldName)) {
                $message = sprintf(
                    'Missing "foreign_field" key for field "%s" in table "%s".',
                    $fieldName,
                    $this->dataType
                );
                throw new \RuntimeException($message, 1376149186);
            }

            // Fetch values from repository.
            $foreignPropertyName = Field::name($foreignFieldName)->of($this)->toPropertyName();
            $findByProperty = 'findBy' . ucfirst($foreignPropertyName);

            // Date picker (type == group) are special fields because property path must contain the table name
            // to determine the relation type. Example for sys_category, property path will look like "items.sys_file"
            $propertyValue = $this->uid;
            if (Tca::table($foreignDataType)->field($foreignFieldName)->isGroup()) {
                $propertyValue = $this->dataType . '.' . $this->uid;
            }

            $this->$propertyName = $foreignContentRepository->$findByProperty($propertyValue);
        } elseif (Tca::table($this->dataType)->field($fieldName)->hasOne()) {
            $fieldConfiguration = Tca::table($this->dataType)->field($fieldName)->getConfiguration();

            // First case, we are on the "good side" of the relation, just query the repository
            if (empty($fieldConfiguration['foreign_field'])) {
                $this->$propertyName = $foreignContentRepository->findByUid($this->$propertyName);
            } else {
                // Second case, we are the "bad side" of the relation, query the foreign repository
                // e.g. in case of one-to-one relation.

                // We must query the opposite side to get the identifier of the foreign object.
                $foreignDataType = Tca::table($this->dataType)->field($fieldName)->getForeignTable();
                $foreignField = Tca::table($this->dataType)->field($fieldName)->getForeignField();
                $foreignContentRepository = ContentRepositoryFactory::getInstance($foreignDataType);
                $find = 'findOneBy' . GeneralUtility::underscoredToUpperCamelCase($foreignField);

                /** @var Content $foreignObject */
                $this->$propertyName = $foreignContentRepository->$find($this->getUid());
            }
        }
        return $this->$propertyName;
    }

    /**
     * @return int
     */
    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function offsetExists($offset): bool
    {
        $offset = Field::name($offset)->of($this)->toPropertyName();
        return isset($this->$offset);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * @return mixed Can return all value types.
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $offset = Field::name($offset)->of($this)->toPropertyName();
        $getter = 'get' . ucfirst($offset);
        return $this->$getter();
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @param mixed $value
     * @return $this
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $offset = Field::name($offset)->of($this)->toPropertyName();
        $setter = 'set' . ucfirst($offset);
        $this->$setter($value);
        return $this;
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @throws NotImplementedException
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $message = 'Un-setting value for Array object is not supported';
        throw new NotImplementedException($message, 1376132306);
    }

    /**
     * Convert this to array
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function toArray(): array
    {
        $result['uid'] = $this->uid;
        $propertiesAndValues = json_decode(json_encode($this), true);

        foreach ($propertiesAndValues as $propertyName => $value) {
            $fieldName = Property::name($propertyName)->of($this)->toFieldName();
            $result[$fieldName] = $value;
        }

        return $result;
    }

    /**
     * Convert this object to an array containing the resolved values.
     *
     * @param bool $resolveRelations
     * @return array
     * @throws \Exception
     */
    public function toValues($resolveRelations = true): array
    {
        $result['uid'] = $this->uid;
        $propertiesAndValues = json_decode(json_encode($this), true);

        foreach ($propertiesAndValues as $propertyName => $value) {
            $fieldName = Property::name($propertyName)->of($this)->toFieldName();

            $result[$fieldName] = $value;
            if ($resolveRelations) {
                $field = Tca::table($this->dataType)->field($fieldName);

                $resolvedValue = '';
                if ($field->getType() === FieldType::FILE) {
                    if ($field->hasMany()) {
                        $files = FileReferenceService::getInstance()->findReferencedBy($propertyName, $this);

                        $resolvedValue = [];
                        foreach ($files as $file) {
                            $resolvedValue[] = $file->getIdentifier();
                        }
                    } else {
                        $files = FileReferenceService::getInstance()->findReferencedBy($propertyName, $this);
                        if (!empty($files)) {
                            $resolvedValue = current($files)->getIdentifier();
                        }
                    }

                    // Reset value
                    $result[$fieldName] = $resolvedValue;
                } elseif (Tca::table($this->dataType)->field($fieldName)->hasRelation()) {
                    $objects = $this[$fieldName];
                    if (is_array($objects)) {
                        $resolvedValue = [];
                        foreach ($objects as $object) {
                            /** @var $object Content */
                            $labelField = Tca::table($object->getDataType())->getLabelField();
                            $resolvedValue[] = $object[$labelField];
                        }
                    } elseif ($objects instanceof Content) {
                        $labelField = Tca::table($objects->getDataType())->getLabelField();
                        $resolvedValue = $objects[$labelField];
                    }

                    // Reset value
                    $result[$fieldName] = $resolvedValue;
                }
            }
        }

        return $result;
    }

    /**
     * Return the properties of this object.
     *
     * @return array
     */
    public function toProperties(): array
    {
        $result[] = 'uid';
        $propertiesAndValues = json_decode(json_encode($this), true);

        foreach ($propertiesAndValues as $propertyName => $value) {
            $result[] = $propertyName;
        }
        return $result;
    }

    /**
     * Return the properties of this object.
     *
     * @return array
     */
    public function toFields(): array
    {
        $result[] = 'uid';
        $propertiesAndValues = json_decode(json_encode($this), true);

        foreach ($propertiesAndValues as $propertyName => $value) {
            $result[] = Property::name($propertyName)->of($this)->toFieldName();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $labelField = Tca::table($this->dataType)->getLabelField();
        return $this[$labelField];
    }

    /**
     * Remove fields according to BE User permission.
     *
     * @param $fields
     * @return array
     * @throws \Exception
     */
    protected function filterForBackendUser($fields): array
    {
        if (!$this->getBackendUser()->isAdmin()) {
            foreach ($fields as $key => $fieldName) {
                if (Tca::table($this->dataType)->hasField($fieldName) && !Tca::table($this->dataType)->field($fieldName)->hasAccess()) {
                    unset($fields[$key]);
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
    protected function filterForConfiguration($fields): array
    {
        $excludedFields = Tca::grid($this->dataType)->getExcludedFields();
        foreach ($fields as $key => $field) {
            if (in_array($field, $excludedFields)) {
                unset($fields[$key]);
            }
        }

        return $fields;
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function isBackendMode(): bool
    {
        return Typo3Mode::isBackendMode();
    }
}
