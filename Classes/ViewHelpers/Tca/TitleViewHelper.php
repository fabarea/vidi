<?php
namespace Fab\Vidi\ViewHelpers\Tca;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Tca\Tca;

/**
 * View helper which returns the title of a content object.
 */
class TitleViewHelper extends AbstractViewHelper
{

    /**
     * Returns the title of a content object.
     *
     * @param Content $content
     * @return string
     */
    public function render(Content $content)
    {
        $table = Tca::table($content->getDataType());
        return $content[$table->getLabelField()];
    }

}
