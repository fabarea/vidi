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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

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
     * @var bool
     */
    protected $canModifyMatcher = false;

    /**
     * Constructor of a Generic Facet in Vidi.
     *
     * @param string $label
     */
    public function __construct($label = '')
    {
        $this->name = 'pid';
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $label = '';
        try {
            $label = LocalizationUtility::translate($this->label, '');
        } catch (\InvalidArgumentException $e) {
        }

        return $label;
    }

    /**
     * @return array
     */
    public function getSuggestions()
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
    protected function getStoragePages()
    {
        $tableName = 'pages';
        $clause = sprintf(
            'uid IN (SELECT DISTINCT(pid) FROM %s WHERE 1=1 %s)',
            $this->getModuleLoader()->getDataType(),
            BackendUtility::deleteClause($this->getModuleLoader()->getDataType())
        );
        $clause .= BackendUtility::deleteClause('pages');

        $pages = $this->getDatabaseConnection()->exec_SELECTgetRows('*', $tableName, $clause);

        return is_array($pages)
            ? $pages
            : [];
    }

    /**
     * Returns a pointer to the database.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
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
    public function hasSuggestions()
    {
        return true;
    }

    /**
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return bool
     */
    public function canModifyMatcher()
    {
        return $this->canModifyMatcher;
    }

    /**
     * @param Matcher $matcher
     * @param $value
     * @return Matcher
     */
    public function modifyMatcher(Matcher $matcher, $value)
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

    /**
     * Magic method implementation for retrieving state.
     *
     * @param array $states
     * @return PageFacet
     */
    static public function __set_state($states)
    {
        return new self($states['name']);
    }

}
