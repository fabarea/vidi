<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Component;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  (c) 2014 Steffen Müller <typo3@t3node.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which renders check.
 */
class CheckPidViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Vidi\ModuleLoader
	 * @inject
	 */
	protected $moduleLoader;

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
	protected $page = NULL;

	/**
	 * A collection of speaking error messages why the pid is invalid.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Pseudo-Constructor, which ensures all dependencies are injected when called.
	 */
	public function initializeObject() {
		$this->dataType = $this->moduleLoader->getDataType();
		$this->configuredPid = $this->getConfiguredPid();
	}

	/**
	 * Renders warnings if storagePid is not properly configured.
	 *
	 * @return string
	 */
	public function render() {
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
	protected function formatMessagePidIsNotValid() {

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
						<li>Settings in the Extension Manager which is the fall-back configuration.</li>
						<li>In some ext_tables.php file, by allowing this record type on any pages.<br />
						\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('{$this->dataType}')
						</li>
						<li>By User TSconfig:</li>
					</ul>
<pre>
# User TSconfig defining default pid for "{$this->dataType}" in Vidi:
tx_vidi {
	dataType {
		{$this->dataType} {
			storagePid = xx
		}
	}
}
</pre>
				</div>
			</div>
EOF;

		return $result;
	}

	/**
	 * Check if pid is 0 and given table is allowed on root level
	 *
	 * @return void
	 */
	protected function validateRootLevel() {
		if ($this->configuredPid > 0) {
			return;
		}

		$isRootLevel = (bool)TcaService::table()->get('rootLevel');
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
	protected function validatePageExist() {
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
	protected function validateDoktype() {
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
	protected function isTableAllowedOnStandardPages() {
		$allowedTables = explode(',', $GLOBALS['PAGES_TYPES']['default']['allowedTables']);
		$result = in_array($this->dataType, $allowedTables);

		return $result;
	}

	/**
	 * Return the default configured pid.
	 *
	 * @return int
	 */
	protected function getConfiguredPid() {

		// Get pid from User TSconfig if any
		$tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->dataType);
		$result = $this->getBackendUser()->getTSConfig($tsConfigPath);
		$configuredPid = (int)$result['value'];

		// If no pid is configured, use default pid from Module Loader
		$pid = ($configuredPid) ?: $this->moduleLoader->getDefaultPid();

		return $pid;
	}

	/**
	 * Returns an instance of the current Backend User.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Return a pointer to the database.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Returns the page record of the configured pid
	 *
	 * @return array
	 */
	public function getPage() {
		if ($this->page !== NULL) {
			return $this->page;
		} else {
			return $this->getDatabaseConnection()->exec_SELECTgetSingleRow('doktype', 'pages', 'deleted = 0 AND uid = ' . $this->configuredPid);
		}
	}
}
