<?php
namespace Fab\Vidi\Module;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class ModulePidService
 */
class ModulePidService
{
    /**
     * The data type (table)
     *
     * @var string
     */
    protected $dataType = '';

    /**
     * A collection of speaking error messages why the pid is invalid.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * ModulePidService constructor.
     */
    public function __construct()
    {
        $this->dataType = $this->getModuleLoader()->getDataType();
    }

    /**
     * Returns a class instance
     *
     * @return \Fab\Vidi\Module\ModulePidService|object
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * @return bool
     */
    public function isConfiguredPidValid(): bool
    {
        $errors = $this->validateConfiguredPid();
        return empty($errors);
    }

    /**
     * @return array
     */
    public function validateConfiguredPid(): array
    {
        $configuredPid = $this->getConfiguredNewRecordPid();
        $this->validateRootLevel($configuredPid);
        $this->validatePageExist($configuredPid);
        $this->validateDoktype($configuredPid);
        return $this->errors;
    }

    /**
     * Return the default configured pid.
     *
     * @return int
     */
    public function getConfiguredNewRecordPid(): int
    {
        if (GeneralUtility::_GP(Parameter::PID)) {
            $configuredPid = (int)GeneralUtility::_GP(Parameter::PID);
        } else {

            // Get pid from User TSConfig if any.
            $tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->dataType);
            $result = $this->getBackendUser()->getTSConfig($tsConfigPath);
            $configuredPid = isset($result['value'])
                ? $configuredPid = (int)$result['value']
                : $this->getModuleLoader()->getDefaultPid();
        }

        return $configuredPid;
    }

    /**
     * Check if pid is 0 and given table is allowed on root level.
     *
     * @param int $configuredPid
     * @return void
     */
    protected function validateRootLevel(int $configuredPid): void
    {
        if ($configuredPid > 0) {
            return;
        }

        $isRootLevel = (bool)Tca::table()->get('rootLevel');
        if (!$isRootLevel) {
            $this->errors[] = sprintf(
                'You are not allowed to use page id "0" unless you set $GLOBALS[\'TCA\'][\'%1$s\'][\'ctrl\'][\'rootLevel\'] = 1;',
                $this->dataType
            );
        }
    }

    /**
     * Check if a page exists for the configured pid
     *
     * @param int $configuredPid
     * @return void
     */
    protected function validatePageExist(int $configuredPid): void
    {
        if ($configuredPid === 0) {
            return;
        }

        $page = $this->getPage($configuredPid);
        if (empty($page)) {
            $this->errors[] = sprintf(
                'No page found for the configured page id "%s".',
                $configuredPid
            );
        }
    }

    /**
     * Check if configured page is a sysfolder and if it is allowed.
     *
     * @param int $configuredPid
     * @return void
     */
    protected function validateDoktype(int $configuredPid): void
    {
        if ($configuredPid === 0) {
            return;
        }

        $page = $this->getPage($configuredPid);
        if (!empty($page)
            && (int)$page['doktype'] !== PageRepository::DOKTYPE_SYSFOLDER
            && !$this->isTableAllowedOnStandardPages()
            && $this->getModuleLoader()->hasComponentInDocHeader(\Fab\Vidi\View\Button\NewButton::class)) {
            $this->errors[] = sprintf(
                'The page with the id "%s" either has to be of the type "folder" (doktype=254) or the table "%s" has to be allowed on standard pages.',
                $configuredPid,
                $this->dataType
            );
        }
    }

    /**
     * Check if given table is allowed on standard pages
     *
     * @return bool
     * @see \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages()
     */
    protected function isTableAllowedOnStandardPages(): bool
    {
        $allowedTables = explode(',', $GLOBALS['PAGES_TYPES']['default']['allowedTables']);
        return in_array($this->dataType, $allowedTables, true);
    }

    /**
     * Returns the page record of the configured pid
     *
     * @param int $configuredPid
     * @return array
     */
    protected function getPage(int $configuredPid): ?array
    {
        $query = $this->getQueryBuilder('pages');
        $query->getRestrictions()->removeAll(); // we are in BE context.

        $page = $query->select('doktype')
            ->from('pages')
            ->where('deleted = 0',
                'uid = ' . $configuredPid)
            ->execute()
            ->fetch();

        return is_array($page)
            ? $page
            : [];
    }

    /**
     * @param string $tableName
     * @return object|QueryBuilder
     */
    protected function getQueryBuilder($tableName): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($tableName);
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Module\ModuleLoader|object
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleLoader::class);
    }

}
