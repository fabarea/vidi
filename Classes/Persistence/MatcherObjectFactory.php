<?php
namespace Fab\Vidi\Persistence;

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

use Fab\Vidi\Module\ModuleName;
use Fab\Vidi\Resolver\FieldPathResolver;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use Fab\Vidi\Module\ModuleLoader;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Factory class related to Matcher object.
 */
class MatcherObjectFactory implements SingletonInterface
{

    /**
     * Gets a singleton instance of this class.
     *
     * @return $this
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Returns a matcher object.
     *
     * @param array $matches
     * @param string $dataType
     * @return Matcher
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     * @throws \InvalidArgumentException
     */
    public function getMatcher(array $matches = array(), $dataType = '')
    {

        if (empty($dataType)) {
            $dataType = $this->getModuleLoader()->getDataType();
        }

        /** @var $matcher Matcher */
        $matcher = GeneralUtility::makeInstance(Matcher::class, array(), $dataType);

        $matcher = $this->applyCriteriaFromDataTables($matcher, $dataType);
        $matcher = $this->applyCriteriaFromMatchesArgument($matcher, $matches);

        if ($this->isBackendMode()) {
            $matcher = $this->applyCriteriaFromUrl($matcher);
            $matcher = $this->applyCriteriaFromTSConfig($matcher);
        }

        // Trigger signal for post processing Matcher Object.
        $this->emitPostProcessMatcherObjectSignal($matcher);

        return $matcher;
    }

    /**
     * Get a possible id from the URL and apply as filter criteria.
     * Except if the main module belongs to the File. The id would be a combined identifier
     * including the storage and a mount point.
     *
     * @param Matcher $matcher
     * @return Matcher $matcher
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     */
    protected function applyCriteriaFromUrl(Matcher $matcher)
    {

        if (GeneralUtility::_GP('id') && $this->getModuleLoader()->getMainModule() !== ModuleName::FILE) {
            $matcher->equals('pid', GeneralUtility::_GP('id'));
        }

        return $matcher;
    }

    /**
     * @param Matcher $matcher
     * @return Matcher $matcher
     * @throws \InvalidArgumentException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     */
    protected function applyCriteriaFromTSConfig(Matcher $matcher)
    {
        $dataType = $this->getModuleLoader()->getDataType();
        $tsConfigPath = sprintf('tx_vidi.dataType.%s.constraints', $dataType);
        $tsConfig = $this->getBackendUser()->getTSConfig($tsConfigPath);

        if (is_array($tsConfig['properties']) && !empty($tsConfig['properties'])) {

            foreach ($tsConfig['properties'] as $constraint) {

                if (preg_match('/(.+) (>=|>|<|<=|=|like) (.+)/is', $constraint, $matches) && count($matches) === 4) {

                    $operator = $matcher->getSupportedOperators()[strtolower(trim($matches[2]))];
                    $operand = trim($matches[1]);
                    $value = trim($matches[3]);

                    $matcher->$operator($operand, $value);
                } elseif (preg_match('/(.+) (in) (.+)/is', $constraint, $matches) && count($matches) === 4) {

                    $operator = $matcher->getSupportedOperators()[trim($matches[2])];
                    $operand = trim($matches[1]);
                    $value = trim($matches[3]);
                    $matcher->$operator($operand, GeneralUtility::trimExplode(',', $value, true));
                }
            }
        }

        return $matcher;
    }

    /**
     * @param Matcher $matcher
     * @param array $matches
     * @return Matcher $matcher
     */
    protected function applyCriteriaFromMatchesArgument(Matcher $matcher, $matches)
    {
        foreach ($matches as $fieldNameAndPath => $value) {
            // CSV values should be considered as "in" operator in the query, otherwise "equals".
            $explodedValues = GeneralUtility::trimExplode(',', $value, true);
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
     * @throws \Exception
     */
    protected function applyCriteriaFromDataTables(Matcher $matcher, $dataType)
    {

        // Special case for Grid in the BE using jQuery DataTables plugin.
        // Retrieve a possible search term from GP.
        $query = GeneralUtility::_GP('search');
        if (is_array($query)) {
            if (!empty($query['value'])) {
                $query = $query['value'];
            } else {
                $query = '';
            }
        }

        if (strlen($query) > 0) {

            // Parse the json query coming from the Visual Search.
            $query = rawurldecode($query);
            $queryParts = json_decode($query, true);

            if (is_array($queryParts)) {
                $matcher = $this->parseQuery($queryParts, $matcher, $dataType);
            } else {
                $matcher->setSearchTerm($query);
            }
        }
        return $matcher;
    }

    /**
     * @param array $queryParts
     * @param Matcher $matcher
     * @param string $dataType
     * @return Matcher $matcher
     * @throws \InvalidArgumentException
     */
    protected function parseQuery(array $queryParts, Matcher $matcher, $dataType)
    {
        foreach ($queryParts as $term) {
            $fieldNameAndPath = key($term);

            $resolvedDataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $dataType);
            $fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $dataType);

            // Retrieve the value.
            $value = current($term);

            if (Tca::grid($resolvedDataType)->hasFacet($fieldName) && Tca::grid($resolvedDataType)->facet($fieldName)->canModifyMatcher()) {
                $matcher = Tca::grid($resolvedDataType)->facet($fieldName)->modifyMatcher($matcher, $value);
            } elseif (Tca::table($resolvedDataType)->hasField($fieldName)) {
                // Check whether the field exists and set it as "equal" or "like".
                if ($this->isOperatorEquals($fieldNameAndPath, $dataType, $value)) {
                    $matcher->equals($fieldNameAndPath, $value);
                } else {
                    $matcher->like($fieldNameAndPath, $value);
                }
            } elseif ($fieldNameAndPath === 'text') {
                // Special case if field is "text" which is a pseudo field in this case.
                // Set the search term which means Vidi will
                // search in various fields with operator "like". The fields come from key "searchFields" in the TCA.
                $matcher->setSearchTerm($value);
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
     * @throws \Exception
     */
    protected function isOperatorEquals($fieldName, $dataType, $value)
    {
        return (Tca::table($dataType)->field($fieldName)->hasRelation() && MathUtility::canBeInterpretedAsInteger($value))
        || Tca::table($dataType)->field($fieldName)->isNumerical();
    }

    /**
     * Signal that is called for post-processing a matcher object.
     *
     * @param Matcher $matcher
     * @signal
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     */
    protected function emitPostProcessMatcherObjectSignal(Matcher $matcher)
    {

        if (strlen($matcher->getDataType()) <= 0) {

            /** @var ModuleLoader $moduleLoader */
            $moduleLoader = $this->getObjectManager()->get(ModuleLoader::class);
            $matcher->setDataType($moduleLoader->getDataType());
        }

        $this->getSignalSlotDispatcher()->dispatch('Fab\Vidi\Controller\Backend\ContentController', 'postProcessMatcherObject', array($matcher, $matcher->getDataType()));
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
     * @throws \InvalidArgumentException
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return ModuleLoader
     * @throws \InvalidArgumentException
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }

    /**
     * @return FieldPathResolver
     * @throws \InvalidArgumentException
     */
    protected function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance(FieldPathResolver::class);
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns whether the current mode is Backend
     *
     * @return bool
     */
    protected function isBackendMode()
    {
        return TYPO3_MODE === 'BE';
    }
}
