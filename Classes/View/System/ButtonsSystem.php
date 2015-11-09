<?php
namespace Fab\Vidi\View\System;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View for rendering buttons in the grids according to a Content object.
 * @todo remove me in version 0.6 + 2 versions
 */
class ButtonsSystem extends AbstractComponentView
{

    /**
     * Rendering buttons in the grids given a Content object.
     *
     * @param Content $object
     * @return string
     */
    public function render(Content $object = NULL)
    {
        return '';
    }

}
