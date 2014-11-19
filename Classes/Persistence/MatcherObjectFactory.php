<?php
namespace TYPO3\CMS\Vidi\Persistence;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Factory class related to Matcher object.
 */
class MatcherObjectFactory implements SingletonInterface {

	/**
	 * Gets a singleton instance of this class.
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\MatcherObjectFactory
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\MatcherObjectFactory');
	}

	/**
	 * Returns a matcher object.
	 *
	 * @param array $matches
	 * @param string $dataType
	 * @return Matcher
	 */
	public function getMatcher(array $matches = array(), $dataType = '') {

		if (empty($dataType)) {
			$dataType = $this->getModuleLoader()->getDataType();
		}

		/** @var $matcher Matcher */
		$matcher = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Matcher', array(), $dataType);

		$matcher = $this->applyCriteriaFromDataTables($matcher, $dataType);
		$matcher = $this->applyCriteriaFromMatchesArgument($matcher, $matches);
		$matcher = $this->applyCriteriaFromUrl($matcher);

		// Trigger signal for post processing Matcher Object.
		$this->emitPostProcessMatcherObjectSignal($matcher);

		return $matcher;
	}

	/**
	 * Apply criteria given by some parameter in the URL.
	 *
	 * @param Matcher $matcher
	 * @return Matcher $matcher
	 */
	protected function applyCriteriaFromUrl(Matcher $matcher) {
		if (GeneralUtility::_GP('id')) {
			$matcher->equals('pid', GeneralUtility::_GP('id'));
		}
		return $matcher;
	}

	/**
	 * Apply criteria specific to jQuery plugin Datatable.
	 *
	 * @param Matcher $matcher
	 * @param array $matches
	 * @return Matcher $matcher
	 */
	protected function applyCriteriaFromMatchesArgument(Matcher $matcher, $matches) {

		foreach ($matches as $fieldNameAndPath => $value) {
			// CSV values should be considered as "in" operator in Query, otherwise "equals".
			$explodedValues = GeneralUtility::trimExplode(',', $value, TRUE);
			if (count($explodedValues) > 1) {
				$matcher->in($fieldNameAndPath, $explodedValues);
			} else {
				$matcher->equals($fieldNameAndPath, $explodedValues[0]);
			}
		}

		return $matcher;
	}

	/**
	 * Apply criteria specific to jQuery plugin DataTable.
	 *
	 * @param Matcher $matcher
	 * @param string $dataType
	 * @return Matcher $matcher
	 */
	protected function applyCriteriaFromDataTables(Matcher $matcher, $dataType) {

		// Special case for Grid in the BE using jQuery DataTables plugin.
		// Retrieve a possible search term from GP.
		$searchTerm = GeneralUtility::_GP('sSearch');

		if (strlen($searchTerm) > 0) {

			// Parse the json query coming from the Visual Search.
			$searchTerm = rawurldecode($searchTerm);
			$terms = json_decode($searchTerm, TRUE);

			if (is_array($terms)) {
				foreach ($terms as $term) {
					$fieldNameAndPath = key($term);

					$resolvedDataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $dataType);
					$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $dataType);

					// Retrieve the value.
					$value = current($term);

					// Check whether the field exists and set it as "equal" or "like".
					if (TcaService::table($resolvedDataType)->hasField($fieldName)) {
						if ($this->isOperatorEquals($fieldNameAndPath, $dataType, $value)) {
							$matcher->equals($fieldNameAndPath, $value);
						} else {
							$matcher->likes($fieldNameAndPath, $value);
						}
					} elseif ($fieldNameAndPath === 'text') {
						// Special case if field is "text" which is a pseudo field in this case.
						// Set the search term which means Vidi will
						// search in various fields with operator "like". The fields come from key "searchFields" in the TCA.
						$matcher->setSearchTerm($value);
					}
				}
			} else {
				$matcher->setSearchTerm($searchTerm);
			}
		}
		return $matcher;
	}

	/**
	 * Tell whether the operator should be equals instead of like for a search, e.g. if the value is numerical.
	 *
	 * @param string $fieldName
	 * @param string $dataType
	 * @param string $value
	 * @return bool
	 */
	protected function isOperatorEquals($fieldName, $dataType, $value) {
		return (TcaService::table($dataType)->field($fieldName)->hasRelation() && MathUtility::canBeInterpretedAsInteger($value))
			|| TcaService::table($dataType)->field($fieldName)->isNumerical();
	}

	/**
	 * Signal that is called for post-processing a matcher object.
	 *
	 * @param Matcher $matcher
	 * @signal
	 */
	protected function emitPostProcessMatcherObjectSignal(Matcher $matcher) {

		if (strlen($matcher->getDataType()) <= 0) {

			/** @var ModuleLoader $moduleLoader */
			$moduleLoader = $this->getObjectManager()->get('TYPO3\CMS\Vidi\Module\ModuleLoader');
			$matcher->setDataType($moduleLoader->getDataType());
		}

		$this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\Controller\Backend\ContentController', 'postProcessMatcherObject', array($matcher, $matcher->getDataType()));
	}

	/**
	 * Get the SignalSlot dispatcher
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		return $this->getObjectManager()->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}

}
