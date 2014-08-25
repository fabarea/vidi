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
		foreach ($GLOBALS['TCA'] as $tableName => $configuration) {
			if (empty($configuration['grid']) && TcaService::table($tableName)->hasLabelField()) {
				$GLOBALS['TCA'][$tableName]['grid'] = $this->getGridTca($tableName);
			}
		}

		return array($GLOBALS['TCA']);
	}

	/**
	 * @param string $tableName
	 * @return array
	 */
	protected function getGridTca($tableName){
		$labelField = TcaService::table($tableName)->getLabelField();

		$tca = array(
			'facets' => array(
				'uid',
				$labelField,
			),
			'columns' => array(
				'__checkbox' => array(
					'width' => '14px',
					'sortable' => FALSE,
					'html' => '<input type="checkbox" class="checkbox-row-top"/>',
				),
				'uid' => array(
					'visible' => FALSE,
					'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:uid', // @todo change me!
					'width' => '5px',
				),
				$labelField => array(
					'editable' => FALSE, // @todo make me editable but consider the record icon with jEditable plugin
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
					'sortable' => FALSE,
					'width' => '70px',
				),
			),
		);

		return $tca;
	}
}