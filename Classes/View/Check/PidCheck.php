<?php
namespace Fab\Vidi\View\Check;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\View\AbstractComponentView;
use TYPO3\CMS\Frontend\Page\PageRepository;
use Fab\Vidi\Module\Parameter;
use Fab\Vidi\Tca\Tca;

/**
 * View which renders check.
 */
class PidCheck extends AbstractComponentView
{

    /**
     * The data type (table)
     *
     * @var string
     */
    protected $dataType = '';

    /**
     * The configured pid for the data type
     *
     * @var int
     */
    protected $configuredPid = 0;

    /**
     * The page record of the configured pid
     *
     * @var array
     */
    protected $page = null;

    /**
     * A collection of speaking error messages why the pid is invalid.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Pseudo-Constructor, which ensures all dependencies are injected when called.
     */
    public function initializeObject()
    {
        $this->dataType = $this->getModuleLoader()->getDataType();
        $this->configuredPid = $this->getConfiguredPid();
    }

    /**
     * Renders warnings if storagePid is not properly configured.
     *
     * @return string
     */
    public function render()
    {
        $result = '';

        $this->validateRootLevel();
        $this->validatePageExist();
        $this->validateDoktype();

        if (!empty($this->errors)) {
            $result .= $this->formatMessagePidIsNotValid();
        }

        return $result;
    }

    /**
     * Format a message whenever the storage is offline.
     *
     * @return string
     */
    protected function formatMessagePidIsNotValid()
    {

        $error = implode('<br />', $this->errors);
        $result = <<< EOF
			<div class="typo3-message message-warning">
				<div class="message-header">
					Page id "{$this->configuredPid}" has found to be a wrong configuration for "{$this->dataType}"
				</div>
				<div class="message-body">
					<p>{$error}</p>
					New records cannot be created with this page id. The configuration can be changed at different levels:
					<ul>
						<li>Settings in the Extension Manager as fallback configuration.</li>
						<li>In some ext_tables.php file, by allowing this record type on any pages.<br />
						\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('{$this->dataType}')
						</li>
						<li>By User TSconfig:</li>
					</ul>
<pre>
# User TSconfig to be placed in your ext_tables.php:
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('

	# Default pid for "{$this->dataType}" in Vidi:
	tx_vidi.dataType.{$this->dataType}.storagePid = xx
');
</pre>

				</div>
			</div>
EOF;

        return $result;
    }

    /**
     * Check if pid is 0 and given table is allowed on root level.
     *
     * @return void
     */
    protected function validateRootLevel()
    {
        if ($this->configuredPid > 0) {
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
     * @return void
     */
    protected function validatePageExist()
    {
        if ($this->configuredPid === 0) {
            return;
        }

        $page = $this->getPage();
        if (empty($page)) {
            $this->errors[] = sprintf(
                'No page found for the configured page id "%s".',
                $this->configuredPid
            );
        }
    }

    /**
     * Check if configured page is a sysfolder and if it is allowed.
     *
     * @return void
     */
    protected function validateDoktype()
    {
        if ($this->configuredPid === 0) {
            return;
        }

        $page = $this->getPage();
        if (!empty($page) && $page['doktype'] != PageRepository::DOKTYPE_SYSFOLDER && !$this->isTableAllowedOnStandardPages()) {
            $this->errors[] = sprintf(
                'The page with the id "%s" either has to be of the type "folder" (doktype=254) or the table "%s" has to be allowed on standard pages.',
                $this->configuredPid,
                $this->dataType
            );
        }
    }

    /**
     * Check if given table is allowed on standard pages
     *
     * @see \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages()
     * @return bool
     */
    protected function isTableAllowedOnStandardPages()
    {
        $allowedTables = explode(',', $GLOBALS['PAGES_TYPES']['default']['allowedTables']);
        $result = in_array($this->dataType, $allowedTables);

        return $result;
    }

    /**
     * Return the default configured pid.
     *
     * @return int
     */
    protected function getConfiguredPid()
    {

        if (GeneralUtility::_GP(Parameter::PID)) {
            $pid = GeneralUtility::_GP(Parameter::PID);
        } else {

            // Get pid from User TSConfig if any.
            $tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->dataType);
            $result = $this->getBackendUser()->getTSConfig($tsConfigPath);
            $configuredPid = (int)$result['value'];

            // If no pid is configured, use default pid from Module Loader
            $pid = ($configuredPid) ?: $this->getModuleLoader()->getDefaultPid();
        }

        return $pid;
    }

    /**
     * Return a pointer to the database.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Returns the page record of the configured pid
     *
     * @return array
     */
    public function getPage()
    {
        if ($this->page !== null) {
            return $this->page;
        } else {
            return $this->getDatabaseConnection()->exec_SELECTgetSingleRow('doktype', 'pages', 'deleted = 0 AND uid = ' . $this->configuredPid);
        }
    }
}
