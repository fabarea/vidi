<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Content;

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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Persistence\Matcher;
use TYPO3\CMS\Vidi\Persistence\Order;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Abstract View helper for handling Content display mainly on the Frontend.
 */
abstract class AbstractContentViewHelper extends AbstractViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('type', 'string', 'Corresponds to the type of data to be fetched. It will basically be a table name e.g. fe_users.', FALSE, '');
		$this->registerArgument('dataType', 'string', 'Corresponds to a table name where the records come from.', FALSE, ''); // @todo deprecated remove me in version 0.6 + 2
		$this->registerArgument('matches', 'array', 'Key / value array to be used as filter. The key corresponds to a field name.', FALSE, array());
		$this->registerArgument('selection', 'int', 'A possible selection defined in the BE and stored in the database.', FALSE, 0);
		$this->registerArgument('ignoreEnableFields', 'bool', 'Whether to ignore enable fields or not (AKA hidden, deleted, starttime, ...).', FALSE, FALSE);
		$this->registerArgument('aliases', 'array', 'Attribute "matches" does not support certain character such as "." in field name. Use this to create aliases.', FALSE, array());
	}

	/**
	 * Generate a signature to be used for storing the result set.
	 *
	 * @param string $dataType
	 * @param array $matches
	 * @param array $orderings
	 * @param $limit
	 * @param $offset
	 * @return string
	 */
	protected function getQuerySignature($dataType, array $matches, array $orderings, $limit, $offset) {
		$serializedMatches = serialize($matches);
		$serializedOrderings = serialize($orderings);
		return md5($dataType . $serializedMatches . $serializedOrderings . $limit . $offset);
	}

	/**
	 * Returns a matcher object.
	 *
	 * @param string $dataType
	 * @param array $matches
	 * @return Matcher
	 */
	protected function getMatcher($dataType, $matches = array()) {

		/** @var $matcher Matcher */
		$matcher = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Matcher', array(), $dataType);

		// @todo implement advanced selection parsing {or: {usergroup.title: {like: foo}}, {tstamp: {greaterThan: 1234}}}
		foreach ($matches as $fieldNameAndPath => $value) {

			// CSV values should be considered as "in" operator in Query, otherwise "equals".
			$explodedValues = GeneralUtility::trimExplode(',', $value, TRUE);

			// The matching value contains a "1,2" as example
			if (count($explodedValues) > 1) {

				$resolvedDataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $dataType);
				$resolvedFieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $dataType);

				// "equals" if in presence of a relation.
				// "in" if not a relation.
				if (TcaService::table($resolvedDataType)->field($resolvedFieldName)->hasRelation()) {
					foreach ($explodedValues as $explodedValue) {
						$matcher->equals($fieldNameAndPath, $explodedValue);
					}
				} else {
					$matcher->in($fieldNameAndPath, $explodedValues);
				}
			} else {
				$matcher->equals($fieldNameAndPath, $explodedValues[0]);
			}
		}

		// Trigger signal for post processing Matcher Object.
		$this->emitPostProcessMatcherObjectSignal($matcher->getDataType(), $matcher);

		return $matcher;
	}

	/**
	 * Replace possible aliases.
	 *
	 * @param array $values
	 * @return array
	 */
	protected function replacesAliases(array $values) {

		$aliases = $this->arguments['aliases'];

		foreach ($aliases as $aliasName => $aliasValue) {
			if (isset($values[$aliasName])) {
				$values[$aliasValue] = $values[$aliasName];
				unset($values[$aliasName]); // remove the alias.
			}
		}

		return $values;
	}

	/**
	 * Returns an order object.
	 *
	 * @param string $dataType
	 * @param array $order
	 * @return \TYPO3\CMS\Vidi\Persistence\Order
	 */
	public function getOrder($dataType, array $order = array()) {
		// Default orderings in case order is empty.
		if (empty($order)) {
			$order = TcaService::table($dataType)->getDefaultOrderings();
		}

		$order = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Order', $order);

		// Trigger signal for post processing Order Object.
		$this->emitPostProcessOrderObjectSignal($dataType, $order);

		return $order;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Persistence\ResultSetStorage
	 */
	public function getResultSetStorage() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\ResultSetStorage');
	}

	/**
	 * Signal that is called for post-processing a "order" object.
	 *
	 * @param string $dataType
	 * @param Order $order
	 * @signal
	 */
	protected function emitPostProcessOrderObjectSignal($dataType, Order $order) {
		$this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessOrderObject', array($order, $dataType));
	}

	/**
	 * Signal that is called for post-processing a "matcher" object.
	 *
	 * @param string $dataType
	 * @param Matcher $matcher
	 * @signal
	 */
	protected function emitPostProcessMatcherObjectSignal($dataType, Matcher $matcher) {
		$this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessMatcherObject', array($matcher, $dataType));
	}

	/**
	 * Signal that is called for post-processing a "limit".
	 *
	 * @param string $dataType
	 * @param int $limit
	 * @signal
	 */
	protected function emitPostProcessLimitSignal($dataType, $limit) {
		$this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessLimit', array($limit, $dataType));
	}

	/**
	 * Signal that is called for post-processing a "offset".
	 *
	 * @param string $dataType
	 * @param int $offset
	 * @signal
	 */
	protected function emitPostProcessOffsetSignal($dataType, $offset) {
		$this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessLimit', array($offset, $dataType));
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
	 * @param $ignoreEnableFields
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
	 */
	protected function getDefaultQuerySettings($ignoreEnableFields) {
		/** @var \TYPO3\CMS\Vidi\Persistence\QuerySettings $defaultQuerySettings */
		$defaultQuerySettings = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\QuerySettings');
		$defaultQuerySettings->setIgnoreEnableFields($ignoreEnableFields);
		return $defaultQuerySettings;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}

}
