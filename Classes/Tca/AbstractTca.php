<?php

namespace Fab\Vidi\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Utility\Typo3Mode;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\ApplicationType;

/**
 * An abstract class to handle TCA.
 */
abstract class AbstractTca implements TcaServiceInterface
{
    /**
     * Returns an instance of the current Backend User.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    protected function isBackendMode(): bool
    {
        return Typo3Mode::isBackendMode();
    }

    protected function isFrontendMode(): bool
    {
        return Typo3Mode::isFrontendMode();
    }
}
