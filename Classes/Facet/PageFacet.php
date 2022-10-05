<?php

namespace Fab\Vidi\Facet;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Module\ModuleLoader;
use Fab\Vidi\Persistence\Matcher;
use Fab\Vidi\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Class for configuring a custom Facet item.
 */
class PageFacet implements FacetInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var bool
     */
    protected $canModifyMatcher = false;

    /**
     * Constructor of a Generic Facet in Vidi.
     *
     * @param string $name
     * @param string $label
     */
    public function __construct($name, $label = '')
    {
        $this->name = 'pid';
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getSuggestions(): array
    {
        $values = [];
        foreach ($this->getStoragePages() as $page) {
            $values[] = [
                $page['uid'] => sprintf('%s (%s)', $page['title'], $page['uid'])
            ];
        }
        return $values;
    }

    /**
     * @return array
     */
    protected function getStoragePages(): array
    {
        /** @var QueryBuilder $query */
        $query = $this->getQueryBuilder('pages');
        $query->getRestrictions()->removeAll();
        return $query->select('*')
            ->from('pages')
            ->where(
                sprintf(
                    'uid IN (SELECT DISTINCT(pid) FROM %s WHERE 1=1 %s)',
                    $this->getModuleLoader()->getDataType(),
                    BackendUtility::deleteClause(
                        $this->getModuleLoader()->getDataType()
                    )
                ),
                BackendUtility::deleteClause('pages', '')
            )
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * @param string $tableName
     * @return object|QueryBuilder
     */
    protected function getQueryBuilder($tableName): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($tableName);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        /** @var LanguageService $langService */
        $langService = $GLOBALS['LANG'];
        if (!$langService) {
            $langService = GeneralUtility::makeInstance(LanguageService::class);
            $langService->init('en');
        }

        return $langService;
    }

    /**
     * @return bool
     */
    public function hasSuggestions(): bool
    {
        return true;
    }

    /**
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType): self
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return bool
     */
    public function canModifyMatcher(): bool
    {
        return $this->canModifyMatcher;
    }

    /**
     * @param Matcher $matcher
     * @param $value
     * @return Matcher
     */
    public function modifyMatcher(Matcher $matcher, $value): Matcher
    {
        return $matcher;
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return ModuleLoader|object
     * @throws \InvalidArgumentException
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }
}
