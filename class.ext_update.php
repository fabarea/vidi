<?php
namespace Fab\Vidi;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Updater script for Ichtus.
 */
class ext_update {

	/**
	 * @return bool
	 */
	public function access() {
		return TRUE;
	}

	/**
	 * @return string
	 */
	public function main() {
		$output[] = $this->updateSelections();

		return sprintf('<ul><li style="float: none">%s</li></ul>', implode('</li><li style="float: none">', $output));
	}

	/**
	 * @return string
	 */
	protected function updateSelections() {

		$tableName = 'tx_vidi_selection';
		$fields = $this->getDatabaseConnection()->admin_get_fields($tableName);

		if (!isset($fields['speaking_query'])) {
			$sql = 'ALTER TABLE tx_vidi_selection ADD speaking_query text;';
			$this->getDatabaseConnection()->sql_query($sql);
		}

		$sql = 'UPDATE tx_vidi_selection SET speaking_query = query WHERE (speaking_query = "" OR speaking_query is NULL) AND query != "";';
		$this->getDatabaseConnection()->sql_query($sql);

		$output = '<br/><strong>Table tx_vidi_selection has been updated</strong><br/>';
		return $output;
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
