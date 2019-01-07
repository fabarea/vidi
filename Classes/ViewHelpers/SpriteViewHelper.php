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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which render an icon using sprites
 */
class SpriteViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'the file to include', true);
    }

    /**
     * Returns an icon using sprites
     *
     * @return string
     */
    public function render()
    {
        return $this->getIconFactory()->getIcon($this->arguments['name'], Icon::SIZE_SMALL);
    }

    /**
     * @return IconFactory|object
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }

}
