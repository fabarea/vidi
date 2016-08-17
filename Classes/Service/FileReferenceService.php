<?php
namespace Fab\Vidi\Service;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
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
    static protected $instances = array();

    /**
     * Returns a class instance
     *
     * @return \Fab\Vidi\Service\FileReferenceService
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance('\Fab\Vidi\Service\FileReferenceService');
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
                self::$instances[$object->getUid()] = array();
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

        $clause = sprintf('tablenames = "%s" %s AND fieldname = "%s" AND uid_foreign = %s',
            $object->getDataType(),
            $this->getWhereClauseForEnabledFields($tableName),
            GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName),
            $object->getUid()
        );
        $rows = $this->getDatabaseConnection()->exec_SELECTgetRows($fileField, $tableName, $clause);

        // Build array of Files
        $files = array();
        foreach ($rows as $row) {
            $files[] = ResourceFactory::getInstance()->getFileObject($row[$fileField]);
        }

        return $files;
    }

    /**
     * get the WHERE clause for the enabled fields of this TCA table
     * depending on the context
     *
     * @param $tableName
     * @return string
     */
    protected function getWhereClauseForEnabledFields($tableName)
    {
        if ($this->isFrontendMode()) {
            // frontend context
            $whereClause = $this->getPageRepository()->enableFields($tableName);
            $whereClause .= $this->getPageRepository()->deleteClause($tableName);
        } else {
            // backend context
            $whereClause = BackendUtility::BEenableFields($tableName);
            $whereClause .= BackendUtility::deleteClause($tableName);
        }
        return $whereClause;
    }

    /**
     * Returns whether the current mode is Frontend
     *
     * @return bool
     */
    protected function isFrontendMode()
    {
        return TYPO3_MODE == 'FE';
    }

    /**
     * Returns an instance of the page repository.
     *
     * @return \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected function getPageRepository()
    {
        return $GLOBALS['TSFE']->sys_page;
    }

    /**
     * Returns a pointer to the database.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
