<?php
namespace Fab\Vidi\Utility;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Standard functions available for the TYPO3 backend.
 */
class BackendUtility
{

    /*******************************************
     *
     * SQL-related, selecting records, searching
     *
     *******************************************/
    /**
     * Returns the WHERE clause " AND NOT [tablename].[deleted-field]" if a deleted-field
     * is configured in $GLOBALS['TCA'] for the tablename, $table
     * This function should ALWAYS be called in the backend for selection on tables which
     * are configured in $GLOBALS['TCA'] since it will ensure consistent selection of records,
     * even if they are marked deleted (in which case the system must always treat them as non-existent!)
     * In the frontend a function, ->enableFields(), is known to filter hidden-field, start- and endtime
     * and fe_groups as well. But that is a job of the frontend, not the backend. If you need filtering
     * on those fields as well in the backend you can use ->BEenableFields() though.
     *
     * @param string $table Table name present in $GLOBALS['TCA']
     * @param string $tableAlias Table alias if any
     * @return string WHERE clause for filtering out deleted records, eg " AND tablename.deleted=0
     */
    public static function deleteClause($table, $tableAlias = '')
    {
        if (empty($GLOBALS['TCA'][$table]['ctrl']['delete'])) {
            return '';
        }
        $expressionBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
            ->expr();
        return ' AND ' . $expressionBuilder->eq(
                ($tableAlias ?: $table) . '.' . $GLOBALS['TCA'][$table]['ctrl']['delete'],
                0
            );
    }
    /**
     * Backend implementation of enableFields()
     * Notice that "fe_groups" is not selected for - only disabled, starttime and endtime.
     * Notice that deleted-fields are NOT filtered - you must ALSO call deleteClause in addition.
     * $GLOBALS["SIM_ACCESS_TIME"] is used for date.
     *
     * @param string $table The table from which to return enableFields WHERE clause. Table name must have a 'ctrl' section in $GLOBALS['TCA'].
     * @param bool $inv Means that the query will select all records NOT VISIBLE records (inverted selection)
     * @return string WHERE clause part
     */
    public static function BEenableFields($table, $inv = false)
    {
        $ctrl = $GLOBALS['TCA'][$table]['ctrl'];
        $expressionBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table)
            ->getExpressionBuilder();
        $query = $expressionBuilder->andX();
        $invQuery = $expressionBuilder->orX();

        if (is_array($ctrl)) {
            if (is_array($ctrl['enablecolumns'])) {
                if ($ctrl['enablecolumns']['disabled'] ?? false) {
                    $field = $table . '.' . $ctrl['enablecolumns']['disabled'];
                    $query->add($expressionBuilder->eq($field, 0));
                    $invQuery->add($expressionBuilder->neq($field, 0));
                }
                if ($ctrl['enablecolumns']['starttime'] ?? false) {
                    $field = $table . '.' . $ctrl['enablecolumns']['starttime'];
                    $query->add($expressionBuilder->lte($field, (int)$GLOBALS['SIM_ACCESS_TIME']));
                    $invQuery->add(
                        $expressionBuilder->andX(
                            $expressionBuilder->neq($field, 0),
                            $expressionBuilder->gt($field, (int)$GLOBALS['SIM_ACCESS_TIME'])
                        )
                    );
                }
                if ($ctrl['enablecolumns']['endtime'] ?? false) {
                    $field = $table . '.' . $ctrl['enablecolumns']['endtime'];
                    $query->add(
                        $expressionBuilder->orX(
                            $expressionBuilder->eq($field, 0),
                            $expressionBuilder->gt($field, (int)$GLOBALS['SIM_ACCESS_TIME'])
                        )
                    );
                    $invQuery->add(
                        $expressionBuilder->andX(
                            $expressionBuilder->neq($field, 0),
                            $expressionBuilder->lte($field, (int)$GLOBALS['SIM_ACCESS_TIME'])
                        )
                    );
                }
            }
        }

        if ($query->count() === 0) {
            return '';
        }

        return ' AND ' . ($inv ? $invQuery : $query);
    }

    /**
     * Returns the URL to a given module
     *
     * @param string $moduleName Name of the module
     * @param array $urlParameters URL parameters that should be added as key value pairs
     * @return string Calculated URL
     */
    public static function getModuleUrl($moduleName, $urlParameters = [])
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        try {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
        } catch (\TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException $e) {
            $uri = $uriBuilder->buildUriFromRoutePath($moduleName, $urlParameters);
        }
        return (string)$uri;
    }

}
