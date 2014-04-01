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
	 * A speaking error message why the pid is invalid.
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Pseudo-Constructor, which ensures all dependencies are injected when called.
	 */
	public function initializeObject() {
		$this->dataType = $this->moduleLoader->getDataType();
		$this->configuredPid = $this->getConfiguredPid();
	}

	/**
	 * Renders a button for uploading assets.
	 *
	 * @return string
	 */
	public function render() {

		$result = '';

		// Check whether storage is configured or not.
		if (!$this->isPidValid()) {
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

		$result = <<< EOF
			<div class="typo3-message message-warning">
				<div class="message-header">
					Page id "{$this->configuredPid}" has found to be a wrong configuration for "{$this->dataType}"
				</div>
				<div class="message-body">
					<p>{$this->error}</p>
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
	 * Check whether the page id is valid or not.
	 *
	 * @return boolean
	 */
	protected function isPidValid() {

		$result = TRUE;

		// Check if the current table is allowed to be used on the rootLevel
		if ($this->configuredPid === 0 && !$this->isTableAllowedOnRootLevel()) {
			$this->error = sprintf(
				'You are not allowed to use page id "0" unless you set $GLOBALS[\'TCA\'][\'%1$s\'][\'ctrl\'][\'rootLevel\'] = 1;',
				$this->dataType
			);
			$result = FALSE;
		}

		// Check if the page exists
		$page = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('doktype', 'pages', 'deleted = 0 AND uid = ' . $this->configuredPid);
		if (empty($page)) {
			$this->error = sprintf(
				'No page found for the configured page id "%s".',
				$this->configuredPid
			);
			$result = FALSE;
		}

		// If the configured page is not a folder, check if it's allowed.
		if (!empty($page) && $page['doktype'] != PageRepository::DOKTYPE_SYSFOLDER && !$this->isTableAllowedOnStandardPages()) {
			$this->error = sprintf(
				'The page with the id "%s" either has to be of the type "folder" (doktype=254) or the table "%s" has to be allowed on standard pages.',
				$this->configuredPid,
				$this->dataType
			);
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Check if given table is allowed on root level
	 *
	 * @return bool
	 */
	protected function isTableAllowedOnRootLevel() {
		$isRootLevel = (bool)TcaService::table()->get('rootLevel');

		return $isRootLevel;
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
}
