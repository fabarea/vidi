<?php

namespace Fab\Vidi\Service;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Tca\Tca;

/**
 * File References service.
 * Find a bunch of file references given by the property name.
 */
class FileReferenceService implements SingletonInterface
{
    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * Returns a class instance
     *
     * @return \Fab\Vidi\Service\FileReferenceService|object
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Service\FileReferenceService::class);
    }

    /**
     * @param Content $object
     * @param string $propertyName
     * @return File[]
     */
    public function findReferencedBy($propertyName, Content $object)
    {
        if (!isset(self::$instances[$object->getUid()][$propertyName])) {
            // Initialize instances value
            if (!isset(self::$instances[$object->getUid()])) {
                self::$instances[$object->getUid()] = [];
            }

            $fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
            $field = Tca::table($object->getDataType())->field($fieldName);
            if ($field->getForeignTable() === 'sys_file_reference') {
                $files = $this->findByFileReference($propertyName, $object);
                self::$instances[$object->getUid()][$propertyName] = $files;
            } else {
                // @todo the standard way of handling file references is by "sys_file_reference". Let see if there is other use cases...
            }
        }

        return self::$instances[$object->getUid()][$propertyName];
    }

    /**
     * Fetch the files given an object assuming
     *
     * @param $propertyName
     * @param Content $object
     * @return File[]
     */
    protected function findByFileReference($propertyName, Content $object)
    {
        $fileField = 'uid_local';
        $tableName = 'sys_file_reference';

        $rows = $this->getDataService()->getRecords(
            $tableName,
            [
                'tablenames' => $object->getDataType(),
                'fieldname'=> GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName),
                'uid_foreign'=> $object->getUid(),
            ]
        );

        // Build array of Files
        $files = [];
        foreach ($rows as $row) {
            $files[] = GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject($row[$fileField]);
        }

        return $files;
    }

    /**
     * @return object|DataService
     */
    protected function getDataService(): DataService
    {
        return GeneralUtility::makeInstance(DataService::class);
    }
}
