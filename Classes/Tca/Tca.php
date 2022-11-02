<?php

namespace Fab\Vidi\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Exception\InvalidKeyInArrayException;
use Fab\Vidi\Module\ModuleLoader;
use Fab\Vidi\Tool\AbstractTool;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Exception\NotExistingClassException;
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
    protected static $systemFields = array(
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
    protected static $instances;

    /**
     * Returns a class instance of a corresponding TCA service.
     * If the class instance does not exist, create one.
     *
     * @throws NotExistingClassException
     * @param string $dataType
     * @param string $serviceType
     * @return TcaServiceInterface
     * @throws InvalidKeyInArrayException
     * @throws \InvalidArgumentException
     */
    protected static function getService($dataType, $serviceType)
    {
        if (AbstractTool::isBackend() && empty($dataType)) {
            /** @var ModuleLoader $moduleLoader */
            $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);
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
     * @return GridService
     * @throws NotExistingClassException
     */
    public static function grid($tableNameOrContentObject = '')
    {
        $tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;
        return self::getService($tableName, self::TYPE_GRID);
    }

    /**
     * Returns a "table" service instance ("ctrl" part of the TCA).
     *
     * @param string|Content $tableNameOrContentObject
     * @return TableService
     * @throws NotExistingClassException
     */
    public static function table($tableNameOrContentObject = '')
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws \InvalidArgumentException
     */
    protected static function emitPreProcessTcaSignal($dataType, $serviceType)
    {
        self::getSignalSlotDispatcher()->dispatch(Tca::class, 'preProcessTca', array($dataType, $serviceType));
    }

    /**
     * Get the SignalSlot dispatcher
     *
     * @return Dispatcher
     * @throws \InvalidArgumentException
     */
    protected static function getSignalSlotDispatcher()
    {
        return GeneralUtility::makeInstance(Dispatcher::class);
    }
}
