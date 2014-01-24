<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Component;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Fabien Udriot <fabien.udriot@typo3.org>
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
					Page id "{$this->getConfiguredPid()}" has be found to be a wrong configuration for "{$this->moduleLoader->getDataType()}"
				</div>
				<div class="message-body">
					New record can not be created with this configured pid. Configuration can be changed at different levels:
					<ul>
						<li>Settings in the Extension Manager which is the fall-back configuration.</li>
						<li>In some ext_tables.php file, by allowing this record type on any pages.<br />
						\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('{$this->moduleLoader->getDataType()}')
						</li>
						<li>By User TSconfig:</li>
					</ul>
<pre>
# User TSconfig defining default pid for "{$this->moduleLoader->getDataType()}" in Vidi:
tx_vidi {
	dataType {
		{$this->moduleLoader->getDataType()} {
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

		$result = FALSE;
		$pageId = $this->getConfiguredPid();

		$isRootLevel = TcaService::table()->get('rootLevel');
		if ($isRootLevel) {
			$result = $this->getConfiguredPid() == 0;
		} else {
			// check if the page id is adequate (folder vs page)
			$page = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('doktype', 'pages', 'deleted = 0 AND uid = ' . $pageId);

			// if different than a folder, check if that is alright
			if (!empty($page) && $page['doktype'] != \TYPO3\CMS\Frontend\Page\PageRepository::DOKTYPE_SYSFOLDER) {
				$allowedTables = explode(',', $GLOBALS['PAGES_TYPES']['default']['allowedTables']);
				$result = in_array($this->moduleLoader->getDataType(), $allowedTables);
			}
		}

		return $result;
	}

	/**
	 * Return the default configured pid.
	 *
	 * @return int
	 */
	protected function getConfiguredPid() {

		// Get configuration from User TSconfig if any
		$tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->moduleLoader->getDataType());
		$result = $this->getBackendUser()->getTSConfig($tsConfigPath);
		$pid = $result['value'];

		// Get pid from Module Loader
		if (NULL === $pid) {
			$pid = $this->moduleLoader->getDefaultPid();
		}
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

?>