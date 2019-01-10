<?php
namespace Fab\Vidi\Utility;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Core\Environment;

/**
 * Extension Management functions
 *
 * This class is never instantiated, rather the methods inside is called as functions like
 * \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('my_extension');
 */
class ExtensionManagementUtility
{
    /**
     * Returns the relative path to the extension as measured from the public web path
     * If the extension is not loaded the function will die with an error message
     * Useful for images and links from the frontend
     *
     * @param string $key Extension key
     * @return string
     */
    public static function siteRelPath($key)
    {
        return self::stripPathSitePrefix(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($key));
    }

    /**
     * Strip first part of a path, equal to the length of public web path including trailing slash
     *
     * @param string $path
     * @return string
     * @internal
     */
    public static function stripPathSitePrefix($path)
    {
        return substr($path, strlen(Environment::getPublicPath() . '/'));
    }

}
