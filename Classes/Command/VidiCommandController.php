<?php
namespace TYPO3\CMS\Vidi\Command;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Command Controller which handles actions related to Vidi.
 */
class VidiCommandController extends CommandController {

	/**
	 * Check TCA configuration for relations used in grid.
	 *
	 * @param string $table the table name. If not defined check for every table.
	 * @return void
	 */
	public function analyseRelationsCommand($table = '') {

		foreach ($GLOBALS['TCA'] as $tableName => $TCA) {

			if ($table != '' && $table !== $tableName) {
				continue;
			}

			$fields = TcaService::grid($tableName)->getFields();
			if (!empty($fields)) {

				$relations = $this->getGridAnalyserService()->checkRelationForTable($tableName);
				if (!empty($relations)) {

					$this->outputLine();
					$this->outputLine('--------------------------------------------------------------------');
					$this->outputLine();
					$this->outputLine(sprintf('Relations for "%s"', $tableName));
					$this->outputLine();
					$this->outputLine(implode("\n", $relations));
				}
			}
		}
	}

	/**
	 * Check labels of the grid.
	 *
	 * @return void
	 */
	public function checkLabelsCommand(){

		// @todo move the logic into the GridAnalyserService.
		$this->outputLine('Checking labels...');
		$result = '-> OK';
		foreach ($GLOBALS['TCA'] as $tableName => $TCA) {
			$tcaGridService = TcaService::grid($tableName);
			foreach ($tcaGridService->getFields() as $fieldName => $configuration) {
				$label = $tcaGridService->getLabel($fieldName);
				if (empty($label) && $tcaGridService->isNotSystem($fieldName)) {
					$output = sprintf('* Missing label for field "%s"', $fieldName);
					$this->outputLine($output);
					$result = '';
				}
			}
		}
		if ($result) {
			$this->outputLine($result);
		}
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\Grid\GridAnalyserService
	 */
	protected function getGridAnalyserService() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Grid\GridAnalyserService');
	}
}
