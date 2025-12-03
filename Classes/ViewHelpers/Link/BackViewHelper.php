<?php

namespace Fab\Vidi\ViewHelpers\Link;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which renders a "back" buttons to be placed in the doc header.
 */
class BackViewHelper extends AbstractViewHelper
{
    /**
     * Returns the "back" buttons to be placed in the doc header.
     *
     * @return string
     */
    public function render()
    {
        $result = '';
        if ($GLOBALS['TYPO3_REQUEST']->getQueryParams()['returnUrl'] ?? null) {
            $result = sprintf(
                '<a href="%s" class="btn btn-default btn-sm btn-return-top">%s</a>',
                $GLOBALS['TYPO3_REQUEST']->getParsedBody()['returnUrl'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['returnUrl'] ?? null,
                $this->getIconFactory()->getIcon('actions-close', Icon::SIZE_SMALL)
            );
        }

        return $result;
    }

    /**
     * @return IconFactory|object
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }
}
