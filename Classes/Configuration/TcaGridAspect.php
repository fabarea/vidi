<?php
namespace TYPO3\CMS\Vidi\Configuration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
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
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
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
					'width' => '5px',
					'sortable' => FALSE,
					'html' => '<input type="checkbox" class="checkbox-row-top"/>',
				),
				'uid' => array(
					'visible' => FALSE,
					'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:uid', // @todo change me!
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
					'sortable' => FALSE,
					'width' => '70px',
				),
			),
		);

		return $tca;
	}
}