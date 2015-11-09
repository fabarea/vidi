<?php
namespace Fab\Vidi\View\Button;

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

use Fab\Vidi\View\Uri\EditUri;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\View\AbstractComponentView;
use Fab\Vidi\Domain\Model\Content;

/**
 * View which renders a "edit" button to be placed in the grid.
 */
class EditButton extends AbstractComponentView
{

    /**
     * Renders a "edit" button to be placed in the grid.
     *
     * @param Content $object
     * @return string
     */
    public function render(Content $object = NULL)
    {
        $editUri = $this->getUriRenderer()->render($object);

        return $this->makeLinkButton()
            ->setHref($editUri)
            ->setDataAttributes([
                'uid' => $object->getUid(),
                'toggle' => 'tooltip',
            ])
            ->setClasses('btn-edit')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:edit'))
            ->setIcon($this->getIconFactory()->getIcon('actions-document-open', Icon::SIZE_SMALL))
            ->render();
    }

    /**
     * @return \Fab\Vidi\View\Uri\EditUri
     */
    protected function getUriRenderer()
    {
        return GeneralUtility::makeInstance(EditUri::class);
    }

}
