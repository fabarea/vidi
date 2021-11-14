<?php
namespace Fab\Vidi\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Exception\NotExistingClassException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * A class to handle TCA ctrl.
 */
class Tca implements SingletonInterface, TcaServiceInterface
{

    /**
     * Fields that are considered as system.
     *
     * @var array
     */
    static protected $systemFields = array(
        'uid',
        'pid',
        'tstamp',
        'crdate',
        'deleted',
        'hidden',
        'sys_language_uid',
        'l18n_parent',
        'l18n_diffsource',
        't3ver_oid',
        't3ver_id',
        't3ver_wsid',
        't3ver_label',
        't3ver_state',
        't3ver_stage',
        't3ver_count',
        't3ver_tstamp',
        't3_origuid',
    );

    /**
     * @var array
     */
    static protected $instances;

    /**
     * Returns a class instance of a corresponding TCA service.
     * If the class instance does not exist, create one.
     *
     * @throws NotExistingClassException
     * @param string $dataType
     * @param string $serviceType of the TCA, TcaServiceInterface::TYPE_TABLE or TcaServiceInterface::TYPE_GRID
     * @return TcaServiceInterface
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     * @throws \InvalidArgumentException
     */
    static protected function getService($dataType = '', $serviceType)
    {
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend() && empty($dataType)) {

            /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
            $moduleLoader = GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleLoader::class);
            $dataType = $moduleLoader->getDataType();
        }

        if (empty(self::$instances[$dataType][$serviceType])) {
            $className = sprintf('Fab\Vidi\Tca\%sService', ucfirst($serviceType));

            // Signal to pre-process the TCA of the given $dataType.
            self::emitPreProcessTcaSignal($dataType, $serviceType);

            $instance = GeneralUtility::makeInstance($className, $dataType, $serviceType);
            self::$instances[$dataType][$serviceType] = $instance;
        }
        return self::$instances[$dataType][$serviceType];
    }

    /**
     * Returns a "grid" service instance.
     *
     * @param string|Content $tableNameOrContentObject
     * @return \Fab\Vidi\Tca\GridService
     * @throws \Fab\Vidi\Exception\NotExistingClassException
     */
    static public function grid($tableNameOrContentObject = '')
    {
        $tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;
        return self::getService($tableName, self::TYPE_GRID);
    }

    /**
     * Returns a "table" service instance ("ctrl" part of the TCA).
     *
     * @param string|Content $tableNameOrContentObject
     * @return \Fab\Vidi\Tca\TableService
     * @throws \Fab\Vidi\Exception\NotExistingClassException
     */
    static public function table($tableNameOrContentObject = '')
    {
        $tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;
        return self::getService($tableName, self::TYPE_TABLE);
    }

    /**
     * @return array
     */
    public static function getInstanceStorage()
    {
        return self::$instances;
    }

    /**
     * @return array
     */
    public static function getSystemFields()
    {
        return self::$systemFields;
    }

    /**
     * Signal that is called after the content repository for a content type has been instantiated.
     *
     * @param string $dataType
     * @param string $serviceType
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \InvalidArgumentException
     */
    static protected function emitPreProcessTcaSignal($dataType, $serviceType)
    {
        self::getSignalSlotDispatcher()->dispatch(Tca::class, 'preProcessTca', array($dataType, $serviceType));
    }

    /**
     * Get the SignalSlot dispatcher
     *
     * @return Dispatcher
     * @throws \InvalidArgumentException
     */
    static protected function getSignalSlotDispatcher()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager->get(Dispatcher::class);
    }

}
