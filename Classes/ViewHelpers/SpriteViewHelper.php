<?php
namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which render an icon using sprites
 */
class SpriteViewHelper extends AbstractViewHelper
{

    /**
     * Returns an icon using sprites
     *
     * @param string $name the file to include
     * @return string
     */
    public function render($name)
    {
        return $this->getIconFactory()->getIcon($name, Icon::SIZE_SMALL);
    }

    /**
     * @return IconFactory
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }

}
