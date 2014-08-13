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
	public function checkRelationsCommand($table = '') {

		foreach ($GLOBALS['TCA'] as $tableName => $TCA) {

			if ($table != '' && $table !== $tableName) {
				continue;
			}

			$fields = TcaService::grid($tableName)->getFields();
			if (!empty($fields)) {

				$relations = $this->checkRelationForTable($tableName);
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
	 * Check relation for table
	 *
	 * @param $tableName
	 * @return array
	 */
	protected function checkRelationForTable($tableName){

		$relations = array();
		$tcaTableService = TcaService::table($tableName);

		$missingOppositionRelationMessage =<<<EOF

  Could not define relation precisely. This is not necessary a problem
  if the opposite relation is not needed. But consider adding the opposite
  TCA configuration if so.';
EOF;

		foreach (TcaService::grid($tableName)->getFields() as $fieldName => $configuration) {

			if ($tcaTableService->hasField($fieldName)) {
				if ($tcaTableService->field($fieldName)->hasMany()) {
					if ($tcaTableService->field($fieldName)->hasRelationWithCommaSeparatedValues()) {
						$_relations = $this->checkRelationOf($tableName, $fieldName, 'comma separated values');
						$relations = array_merge($relations, $_relations);
					} elseif ($tcaTableService->field($fieldName)->hasRelationManyToMany()) {
						$_relations = $this->checkRelationManyToMany($tableName, $fieldName);
						$relations = array_merge($relations, $_relations);

					} elseif ($tcaTableService->field($fieldName)->hasRelationOneToMany()) {
						$_relations = $this->checkRelationOf($tableName, $fieldName, 'one-to-many');
						$relations = array_merge($relations, $_relations);
					} else {
						$relations[] = '* WARNING!';
						$relations[] = sprintf('  ?-to-many "%s"%s', $fieldName, $missingOppositionRelationMessage);
					}
				} elseif ($tcaTableService->field($fieldName)->hasOne()) {

					if ($tcaTableService->field($fieldName)->hasRelationOneToOne()) {
						$relations[] = sprintf('* one-to-one "%s"', $fieldName);
					} elseif ($tcaTableService->field($fieldName)->hasRelationManyToOne()) {
						$_relations = $this->checkRelationOf($tableName, $fieldName, 'many-to-one');
						$relations = array_merge($relations, $_relations);
					} else {
						$relations[] = '* WARNING!';
						$relations[] = sprintf('  ?-to-one "%s"%s', $fieldName, $missingOppositionRelationMessage);
					}
				}
			}
		}
		return $relations;
	}

	/**
	 * Convenience method for printing out relation many-to-many.
	 *
	 * @param string $tableName
	 * @param string $fieldName
	 * @return array
	 */
	protected function checkRelationManyToMany($tableName, $fieldName) {

		$output = array();

		$tcaTableService = TcaService::table($tableName);
		$output[] = sprintf('* %s (many-to-many)', $fieldName);

		$foreignTable = $tcaTableService->field($fieldName)->getForeignTable();
		$manyToManyTable = $tcaTableService->field($fieldName)->getManyToManyTable();
		$foreignField = $tcaTableService->field($fieldName)->getForeignField();

		if (!$foreignField) {
			$output[] = sprintf('  ERROR! Can not found foreign field for "%s". Perhaps missing opposite configuration?', $fieldName);
		} elseif (!$foreignTable) {
			$output[] = sprintf('  ERROR! Can not found foreign table for "%s". Perhaps missing opposite configuration?', $fieldName);
		} elseif (!$manyToManyTable) {
			$output = sprintf('  ERROR! Can not found relation table (MM) for "%s". Perhaps missing opposite configuration?', $fieldName);
		} else {
			$output[] = sprintf('  %s.%s --> %s --> %s.%s', $tableName, $fieldName, $manyToManyTable, $foreignTable, $foreignField);
		}

		$output[] = '';
		return $output;
	}

	/**
	 * Convenience method for printing out relation.
	 *
	 * @param string $tableName
	 * @param string $fieldName
	 * @param string $relationType
	 * @return array
	 */
	protected function checkRelationOf($tableName, $fieldName, $relationType) {

		$output = array();

		$tcaTableService = TcaService::table($tableName);
		$output[] = sprintf('* %s "%s" ', $relationType, $fieldName);

		$foreignTable = $tcaTableService->field($fieldName)->getForeignTable();
		$foreignField = $tcaTableService->field($fieldName)->getForeignField();
		$output[] = sprintf('  %s.%s --> %s.%s', $tableName, $fieldName, $foreignTable, $foreignField);
		$output[] = '';

		return $output;
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
}
