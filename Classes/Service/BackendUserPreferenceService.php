<?php
namespace Fab\Vidi\Service;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class dealing with BE User preference.
 */
class BackendUserPreferenceService
{

    /**
     * Returns a class instance
     *
     * @return \Fab\Vidi\Service\BackendUserPreferenceService|object
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Service\BackendUserPreferenceService::class);
    }

    /**
     * Returns a configuration key for the current BE User.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $result = '';
        if ($this->getBackendUser() && !empty($this->getBackendUser()->uc[$key])) {
            $result = $this->getBackendUser()->uc[$key];

        }
        return $result;
    }

    /**
     * Set a configuration for the current BE User.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        if ($this->getBackendUser()) {
            $this->getBackendUser()->uc[$key] = $value;
            $this->getBackendUser()->writeUC();
        }
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
}
