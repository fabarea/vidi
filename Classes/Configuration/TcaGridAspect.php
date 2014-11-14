<?php
namespace TYPO3\CMS\Vidi\Configuration;

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

use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Vidi\Grid\ButtonGroupComponent;
use TYPO3\CMS\Vidi\Grid\CheckBoxComponent;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Add a Grid TCA to each "data type" enabling to display a Vidi module in the BE.
 */
class TcaGridAspect implements TableConfigurationPostProcessingHookInterface {

	/**
	 * Scans each data type of the TCA and add a Grid TCA if missing.
	 *
	 * @return array
	 */
	public function processData() {
		foreach ($GLOBALS['TCA'] as $dataType => $configuration) {
			if (empty($configuration['grid']) && $this->hasLabelField($dataType)) {
				$GLOBALS['TCA'][$dataType]['grid'] = $this->getGridTca($dataType);
			}
		}

		return array($GLOBALS['TCA']);
	}

	/**
	 * @param string $tableName
	 * @return array
	 */
	protected function getGridTca($tableName) {
		$labelField = $this->getLabelField($tableName);

		$tca = array(
			'facets' => array(
				'uid',
				$labelField,
			),
			'columns' => array(
				'__checkbox' => array(
					'renderer' => new CheckBoxComponent(),
				),
				'uid' => array(
					'visible' => FALSE,
					'label' => 'Id',
					'width' => '5px',
				),
				$labelField => array(
					'editable' => TRUE,
				),
				'tstamp' => array(
					'visible' => FALSE,
					'format' => 'date',
					'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:tstamp',
				),
				'crdate' => array(
					'visible' => FALSE,
					'format' => 'date',
					'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:crdate',
				),
				'__buttons' => array(
					'renderer' => new ButtonGroupComponent(),
				),
			),
		);

		return $tca;
	}

	/**
	 * Get the label name of table name.
	 *
	 * @param string $dataType
	 * @return bool
	 */
	protected function getLabelField($dataType) {
		return $GLOBALS['TCA'][$dataType]['ctrl']['label'];
	}

	/**
	 * Tell whether the table has a label field.
	 *
	 * @param string $dataType
	 * @return bool
	 */
	protected function hasLabelField($dataType) {
		return isset($GLOBALS['TCA'][$dataType]['ctrl']['label']);
	}
}