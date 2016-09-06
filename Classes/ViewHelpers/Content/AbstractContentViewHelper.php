<?php
namespace Fab\Vidi\ViewHelpers\Content;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Persistence\QuerySettings;
use Fab\Vidi\Persistence\ResultSetStorage;
use Fab\Vidi\Resolver\FieldPathResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Persistence\Matcher;
use Fab\Vidi\Persistence\Order;
use Fab\Vidi\Tca\Tca;

/**
 * Abstract View helper for handling Content display mainly on the Frontend.
 */
abstract class AbstractContentViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument('type', 'string', 'Corresponds to the type of data to be fetched. It will basically be a table name e.g. fe_users.', false, '');
        $this->registerArgument('matches', 'array', 'Key / value array to be used as filter. The key corresponds to a field name.', false, array());
        $this->registerArgument('selection', 'int', 'A possible selection defined in the BE and stored in the database.', false, 0);
        $this->registerArgument('ignoreEnableFields', 'bool', 'Whether to ignore enable fields or not (AKA hidden, deleted, starttime, ...).', false, false);
        $this->registerArgument('aliases', 'array', 'Attribute "matches" does not support certain character such as "." in field name. Use this to create aliases.', false, array());
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
    protected function getQuerySignature($dataType, array $matches, array $orderings, $limit, $offset)
    {
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
     * @throws \Fab\Vidi\Exception\NotExistingClassException
     * @throws \InvalidArgumentException
     */
    protected function getMatcher($dataType, $matches = array())
    {

        /** @var $matcher Matcher */
        $matcher = GeneralUtility::makeInstance(Matcher::class, [], $dataType);

        // @todo implement advanced selection parsing {or: {usergroup.title: {like: foo}}, {tstamp: {greaterThan: 1234}}}
        foreach ($matches as $fieldNameAndPath => $value) {

            // CSV values should be considered as "in" operator in Query, otherwise "equals".
            $explodedValues = GeneralUtility::trimExplode(',', $value, true);

            // The matching value contains a "1,2" as example
            if (count($explodedValues) > 1) {

                $resolvedDataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $dataType);
                $resolvedFieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $dataType);

                // "equals" if in presence of a relation.
                // "in" if not a relation.
                if (Tca::table($resolvedDataType)->field($resolvedFieldName)->hasRelation()) {
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
    protected function replacesAliases(array $values)
    {

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
     * @return Order
     */
    public function getOrder($dataType, array $order = array())
    {
        // Default orderings in case order is empty.
        if (empty($order)) {
            $order = Tca::table($dataType)->getDefaultOrderings();
        }

        $order = GeneralUtility::makeInstance(Order::class, $order);

        // Trigger signal for post processing Order Object.
        $this->emitPostProcessOrderObjectSignal($dataType, $order);

        return $order;
    }

    /**
     * @return ResultSetStorage
     */
    public function getResultSetStorage()
    {
        return GeneralUtility::makeInstance(ResultSetStorage::class);
    }

    /**
     * Signal that is called for post-processing a "order" object.
     *
     * @param string $dataType
     * @param Order $order
     * @signal
     */
    protected function emitPostProcessOrderObjectSignal($dataType, Order $order)
    {
        $this->getSignalSlotDispatcher()->dispatch('Fab\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessOrderObject', array($order, $dataType));
    }

    /**
     * Signal that is called for post-processing a "matcher" object.
     *
     * @param string $dataType
     * @param Matcher $matcher
     * @signal
     */
    protected function emitPostProcessMatcherObjectSignal($dataType, Matcher $matcher)
    {
        $this->getSignalSlotDispatcher()->dispatch('Fab\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessMatcherObject', array($matcher, $dataType));
    }

    /**
     * Signal that is called for post-processing a "limit".
     *
     * @param string $dataType
     * @param int $limit
     * @signal
     */
    protected function emitPostProcessLimitSignal($dataType, $limit)
    {
        $this->getSignalSlotDispatcher()->dispatch('Fab\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessLimit', array($limit, $dataType));
    }

    /**
     * Signal that is called for post-processing a "offset".
     *
     * @param string $dataType
     * @param int $offset
     * @signal
     */
    protected function emitPostProcessOffsetSignal($dataType, $offset)
    {
        $this->getSignalSlotDispatcher()->dispatch('Fab\Vidi\ViewHelper\Content\AbstractContentViewHelper', 'postProcessLimit', array($offset, $dataType));
    }

    /**
     * Get the SignalSlot dispatcher
     *
     * @return Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        return $this->getObjectManager()->get(Dispatcher::class);
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @param $ignoreEnableFields
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
     */
    protected function getDefaultQuerySettings($ignoreEnableFields)
    {
        /** @var QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = GeneralUtility::makeInstance(QuerySettings::class);
        $defaultQuerySettings->setIgnoreEnableFields($ignoreEnableFields);
        return $defaultQuerySettings;
    }

    /**
     * @return FieldPathResolver
     */
    protected function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance(FieldPathResolver::class);
    }

}
