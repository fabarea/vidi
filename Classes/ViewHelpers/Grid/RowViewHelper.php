<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Formatter\FormatterInterface;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper for rendering a row of a content object.
 */
class RowViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var array
	 */
	protected $columns = array();

	/**
	 * @var array
	 */
	protected $relations = array();

	/**
	 * @param array $columns
	 */
	public function __construct($columns = array()){
		$this->columns = $columns;
	}

	/**
	 * Render a row per content object.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param int $offset
	 * @return array
	 */
	public function render(Content $object, $offset) {


		// Initialize returned array
		$output = array();

		foreach(TcaService::grid()->getFields() as $fieldName => $configuration) {

			if (TcaService::grid()->isSystem($fieldName)) {

				$systemFieldName = substr($fieldName, 2);
				$className = sprintf('TYPO3\CMS\Vidi\ViewHelpers\Grid\System%sViewHelper', ucfirst($systemFieldName));
				if (class_exists($className)) {

					/** @var \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper $systemColumnViewHelper */
					$systemColumnViewHelper = $this->objectManager->get($className);
					$output[$fieldName] = $systemColumnViewHelper->render($object, $offset);
				}
			} elseif (!in_array($fieldName, $this->columns) && !TcaService::grid()->isForce($fieldName)) {

				// Show nothing if the column is not requested which is good for performance.
				$output[$fieldName] = '';
			} else {

				$this->handleRelation($object, $fieldName);

				// Fetch value
				if (TcaService::grid()->hasRenderers($fieldName)) {

					$result = '';
					$renderers = TcaService::grid()->getRenderers($fieldName);

					// if is relation has one
					foreach ($renderers as $rendererClassName => $rendererConfiguration) {

						/** @var $rendererObject \TYPO3\CMS\Vidi\Grid\GridRendererInterface */
						$rendererObject = GeneralUtility::makeInstance($rendererClassName);
						$result .= $rendererObject
							->setObject($object)
							->setFieldName($fieldName)
							->setFieldConfiguration($configuration)
							->setGridRendererConfiguration($rendererConfiguration)
							->render();
					}
				} else {

					// Retrieve the content from the field.
					$result = $object[$fieldName] instanceof Content ? $object[$fieldName]['uid'] : $object[$fieldName]; // AccessArray object

					// Avoid bad surprise, converts characters to HTML.
					$fieldType = TcaService::table($object->getDataType())->field($fieldName)->getFieldType();
					if ($fieldType !== TcaService::TEXTAREA) {
						$result = htmlentities($result);
					} elseif ($fieldType === TcaService::TEXTAREA && !$this->isClean($result)) {
						$result = htmlentities($result);
					} elseif ($fieldType === TcaService::TEXTAREA && !$this->hasHtml($result)) {
						$result = nl2br($result);
					}
				}

				$result = $this->format($result, $configuration);
				$result = $this->wrap($result, $configuration);
				$output[$fieldName] = $result;
			}
		}

		$output['DT_RowId'] = 'row-' . $object->getUid();
		$output['DT_RowClass'] = sprintf('%s_%s %s',
			$object->getDataType(),
			$object->getUid(),
			implode(' ', $this->relations)
		);

		return $output;
	}

	/**
	 * Handle if the field to be outputted has a relation.
	 * Collect this relations along the way to be displayed in the final JSON.
	 *
	 * @param Content $object
	 * @param string $fieldName
	 * @return void
	 */
	protected function handleRelation(Content $object, $fieldName) {

		// It must be resolved.
		$dataType = TcaService::grid()->getDataType($fieldName);
		if ($object->getDataType() == $dataType
			&& TcaService::table()->hasField($fieldName)
			&& TcaService::table()->field($fieldName)->hasRelationOneToOne()) {

			$foreignDataType = TcaService::table()->field($fieldName)->getForeignTable();

			// Check if the relation is handle on this side or on the opposite side.
			if (!empty($object[$fieldName])) {
				$this->relations[] = $foreignDataType . '_' . $object[$fieldName]['uid'];
			} else {
				// We must query the opposite side to get the identifier of the foreign object.
				$foreignField = TcaService::table()->field($fieldName)->getForeignField();
				$foreignDataType = TcaService::table()->field($fieldName)->getForeignTable();
				$foreignField = TcaService::table()->field($fieldName)->getForeignField();
				$foreignRepository = ContentRepositoryFactory::getInstance($foreignDataType);
				$find = 'findOneBy' . GeneralUtility::underscoredToUpperCamelCase($foreignField);

				/** @var Content $foreignObject */
				$foreignObject = $foreignRepository->$find($object->getUid());
				$this->relations[] = $foreignDataType . '_' . $foreignObject->getUid();
				$object[$fieldName] = $foreignObject;
			}
		}
	}

	/**
	 * Check whether a string contains HTML tags.
	 *
	 * @param string $content the content to be analyzed
	 * @return boolean
	 */
	protected function hasHtml($content) {
		$result = FALSE;

		// We compare the length of the string with html tags and without html tags.
		if (strlen($content) != strlen(strip_tags($content))) {
			$result = TRUE;
		}
		return $result;
	}

	/**
	 * Check whether a string contains potential XSS.
	 *
	 * @param string $content the content to be analyzed
	 * @return boolean
	 */
	protected function isClean($content) {

		// @todo implement me!
		$result = TRUE;
		return $result;
	}

	/**
	 * Possible value formatting.
	 *
	 * @param string $value
	 * @param array $configuration
	 * @return mixed
	 */
	protected function format($value, array $configuration) {
		if (empty($configuration['format'])) {
			return $value;
		}
		$className = $configuration['format'];

		// Support legacy formatter names which are not full qualified class names.
		if (!class_exists($className)) {
			$message = 'The Ext:vidi Grid configuration option "format" needs to be a full qualified class name since version 0.3.0.';
			$message .= 'Support for "date" and "datetime" will be removed two versions later.';
			GeneralUtility::deprecationLog($message);

			$className = 'TYPO3\\CMS\\Vidi\\Formatter\\' . ucfirst($className);
		}

		/** @var \TYPO3\CMS\Vidi\Formatter\FormatterInterface $formatter */
		$formatter = $this->objectManager->get($className);
		$value = $formatter->format($value);

		return $value;
	}

	/**
	 * Possible value wrapping.
	 *
	 * @param string $value
	 * @param array $configuration
	 * @return mixed
	 */
	protected function wrap($value, array $configuration) {
		if (!empty($configuration['wrap'])) {
			$parts = explode('|', $configuration['wrap']);
			$value = implode($value, $parts);
		}
		return $value;
	}
}
