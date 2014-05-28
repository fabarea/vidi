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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which renders check.
 */
class CheckRelationsViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Vidi\ModuleLoader
	 * @inject
	 */
	protected $moduleLoader;

	/**
	 * @var array
	 */
	protected $invalidFields = array();

	/**
	 * Renders a button for uploading assets.
	 *
	 * @return string
	 */
	public function render() {

		$result = '';

		// Check whether storage is configured or not.
		if (!$this->isTcaValid()) {
			$result .= $this->formatMessageTcaIsNotValid();
		}

		return $result;
	}

	/**
	 * Format a message whenever the storage is offline.
	 *
	 * @return string
	 */
	protected function formatMessageTcaIsNotValid() {

		$result = <<< EOF
			<div class="typo3-message message-warning">
				<div class="message-header">
					Grid may have trouble to render because of wrong / missing TCA.
				</div>
				<div class="message-body">
					TCA which describes the relations within "{$this->moduleLoader->getDataType()}" is incorrect.
					When dealing with MM relations, Vidi requires to have a TCA in both direction to work properly.

					You could try the following fix:

					{$this->formatMessageHelperText()}
				</div>
			</div>
EOF;
		return $result;
	}

	/**
	 * Check relations of current data type in the Grid.
	 *
	 * @return string
	 */
	protected function formatMessageHelperText() {
		$helperText = '';
		foreach ($this->invalidFields as $invalidField) {
			$helperText .= <<<EOF
				<br />
				In file EXT:my_ext/Configuration/TCA/{$invalidField}.php
<pre>
\$tca = array(
	'columns' => array(
		'CHANGE_ME' => array(
			'config' => array(
				'type' => 'select',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 9999,
				'autoSizeMax' => 30,
				'multiple' => 0,
				'foreign_table' => '{$this->moduleLoader->getDataType()}',
				'MM' => 'CHANGE_ME',
				'MM_opposite_field' => '{$invalidField}',
			),
		),
	),
);

if (!empty(\$GLOBALS['TCA']['{$invalidField}'])) {
	return \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule(\$GLOBALS['TCA']['{$invalidField}'], \$tca);
}
</pre>
EOF;
		}
		return $helperText;
	}

	/**
	 * Check relations of current data type in the Grid.
	 *
	 * @return boolean
	 */
	protected function isTcaValid() {

		$tableName = $this->moduleLoader->getDataType();
		$tcaGridService = TcaService::grid($tableName);
		$tcaTableService = TcaService::table($tableName);

		foreach ($tcaGridService->getFields() as $fieldName => $configuration) {

			if ($tcaGridService->isNotSystem($fieldName) && $tcaTableService->field($fieldName)->hasRelationMany()) {
				if ($tcaTableService->field($fieldName)->hasRelationManyToMany()) {

					$foreignTable = $tcaTableService->field($fieldName)->getForeignTable();
					$manyToManyTable = $tcaTableService->field($fieldName)->getManyToManyTable();
					$foreignField = $tcaTableService->field($fieldName)->getForeignField();

					if (!$foreignField) {
						$this->invalidFields[] = $fieldName;
					} elseif (!$foreignTable) {
						$this->invalidFields[] = $fieldName;
					} elseif (!$manyToManyTable) {
						$this->invalidFields[] = $fieldName;
					}
				}
			}
		}

		return empty($this->invalidFields);
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
