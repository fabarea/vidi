<?php

namespace Fab\Vidi\Service;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use Fab\Vidi\Persistence\Matcher;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to interact with the Vidi clipboard.
 */
class ClipboardService implements SingletonInterface
{
    /**
     * Get the Matcher object of the clipboard.
     *
     * @return Matcher
     */
    public function getMatcher()
    {
        $matcher = $this->getBackendUser()->getModuleData($this->getDataKey());
        if (!$matcher) {
            /** @var $matcher Matcher */
            $matcher = GeneralUtility::makeInstance(Matcher::class);
        }
        return $matcher;
    }

    /**
     * Tell whether the clipboard has items or not.
     *
     * @return bool
     */
    public function hasItems()
    {
        $matcher = $this->getMatcher();

        $inCriteria = $matcher->getIn();
        $likeCriteria = $matcher->getLike();
        $searchTerm = $matcher->getSearchTerm();

        $hasItems = !empty($inCriteria) || !empty($likeCriteria) || !empty($searchTerm);
        return $hasItems;
    }

    /**
     * Save data into the clipboard.
     *
     * @param Matcher $matches
     */
    public function save(Matcher $matches)
    {
        $this->getBackendUser()->pushModuleData($this->getDataKey(), $matches);
    }

    /**
     * Completely empty the clipboard for a data type.
     *
     * @return void
     */
    public function flush()
    {
        $this->getBackendUser()->pushModuleData($this->getDataKey(), null);
    }

    /**
     * @return string
     */
    protected function getDataKey()
    {
        return 'vidi_clipboard_' . $this->getModuleLoader()->getDataType();
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return ModuleLoader|object
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
