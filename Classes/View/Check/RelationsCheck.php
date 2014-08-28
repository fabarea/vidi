<?php
namespace TYPO3\CMS\Vidi\View\Check;

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

use TYPO3\CMS\Vidi\View\AbstractComponentView;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View which renders check.
 */
class RelationsCheck extends AbstractComponentView {

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
					TCA which describes the relations within "{$this->getModuleLoader()->getDataType()}" is incorrect.
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
				'foreign_table' => '{$this->getModuleLoader()->getDataType()}',
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

		$dataType = $this->getModuleLoader()->getDataType();
		$grid = TcaService::grid($dataType);
		$table = TcaService::table($dataType);

		foreach ($grid->getFields() as $fieldName => $configuration) {

			if ($grid->isNotSystem($fieldName) && $table->field($fieldName)->hasMany()) {
				if ($table->field($fieldName)->hasRelationManyToMany()) {

					$foreignTable = $table->field($fieldName)->getForeignTable();
					$manyToManyTable = $table->field($fieldName)->getManyToManyTable();
					$foreignField = $table->field($fieldName)->getForeignField();

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

}
