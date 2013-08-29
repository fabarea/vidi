<?php
namespace TYPO3\CMS\Vidi\Command;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * Command Controller which handles actions related to Vidi.
 *
 * @author Fabien Udriot <fabien.udriot@typo3.org>
 * @package TYPO3
 * @subpackage media
 */
class VidiCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * Check TCA configuration for relations used in grid.
	 *
	 * @param string $table the table name. If not defined check for every table.
	 * @return void
	 */
	public function checkRelationsCommand($table = '') {

		foreach ($GLOBALS['TCA'] as $tableName => $TCA) {

			if ($table != '' && $table !== $tableName) {
				continue;
			}
			$tcaGridService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService($tableName);

			$fields = $tcaGridService->getFields();
			if (!empty($fields)) {
				$this->outputLine(sprintf('Relations used in grid for table %s', $tableName));
				$this->outputLine('--------------------------------------------------------------------');
				$this->outputLine();

				$hasRelation = $this->checkRelationForTable($tableName);
				if (!$hasRelation) {
					$this->outputLine('No relation found!');
					$this->outputLine();
				}
			}
		}
	}

	/**
	 * Check relation for table
	 *
	 * @param $tableName
	 * @return bool
	 */
	protected function checkRelationForTable($tableName){

		$tcaGridService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService($tableName);
		$tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($tableName);

		$hasRelation = FALSE;
		foreach ($tcaGridService->getFields() as $fieldName => $configuration) {

			if ($tcaFieldService->hasRelationMany($fieldName)) {

				$hasRelation = TRUE;

				if ($tcaFieldService->hasRelationWithCommaSeparatedValues($fieldName)) {
					$this->printRelation($tableName, $fieldName, 'comma separated values');
				} elseif ($tcaFieldService->hasRelationManyToMany($fieldName)) {
					$output = sprintf('* %s (many-to-many) @todo write something more explicit.', $fieldName);
					$this->outputLine($output);
				} elseif ($tcaFieldService->hasRelationManyToOne($fieldName)) {
					$this->printRelation($tableName, $fieldName, 'many-to-one');
				} else {
					$output = sprintf('* NOTICE: %s (many-to-?). Could not define relation type precisely. Missing opposite TCA configuration?', $fieldName);
					$this->outputLine($output);
				}
			}

			if ($tcaFieldService->hasRelationOne($fieldName)) {

				$hasRelation = TRUE;

				if ($tcaFieldService->hasRelationOneToOne($fieldName)) {
					$output = sprintf('* %s (one-to-one)', $fieldName);
					$this->outputLine($output);
				} elseif ($tcaFieldService->hasRelationOneToMany($fieldName)) {
					$this->printRelation($tableName, $fieldName, 'one-to-many');
				} else {
					$output = sprintf('* NOTICE: %s (one-to-?). Could not define relation type precisely. Missing opposite TCA configuration?', $fieldName);
					$this->outputLine($output);
				}
			}
		}

		if ($hasRelation) {
			$this->outputLine();
		}
		return $hasRelation;
	}

	/**
	 * Convenience method for printing out relation.
	 *
	 * @param string $tableName
	 * @param string $fieldName
	 * @param string $relationType
	 * @return void
	 */
	protected function printRelation($tableName, $fieldName, $relationType) {

		$tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($tableName);
		$output = sprintf('* %s (%s)', $fieldName, $relationType);
		$this->outputLine($output);

		$foreignTable = $tcaFieldService->getForeignTable($fieldName);
		$foreignField = $tcaFieldService->getForeignField($fieldName);
		$output = sprintf('  %s.%s --> %s.%s', $tableName, $fieldName, $foreignTable, $foreignField);
		$this->outputLine($output);
	}

	/**
	 * Check labels of the grid.
	 *
	 * @return void
	 */
	public function checkLabelsCommand(){

		$this->outputLine('Checking labels...');
		$result = '-> OK';
		foreach ($GLOBALS['TCA'] as $tableName => $TCA) {
			$tcaGridService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService($tableName);
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
}
