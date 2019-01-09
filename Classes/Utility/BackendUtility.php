<?php
namespace Fab\Vidi\Utility;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Backend\Backend\Shortcut\ShortcutRepository;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\BackendWorkspaceRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\PseudoSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Frontend\Compatibility\LegacyDomainResolver;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Standard functions available for the TYPO3 backend.
 * You are encouraged to use this class in your own applications (Backend Modules)
 * Don't instantiate - call functions with "\TYPO3\CMS\Backend\Utility\BackendUtility::" prefixed the function name.
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
