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

use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Imaging\Icon;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View which renders a "delete" button to be placed in the grid.
 */
class DeleteButton extends AbstractComponentView
{

    /**
     * Renders a "delete" button to be placed in the grid.
     *
     * @param Content $object
     * @return string
     */
    public function render(Content $object = NULL)
    {
        $labelField = Tca::table($object->getDataType())->getLabelField();
        $label = $object[$labelField] ? $object[$labelField] : $object->getUid();

        return $this->makeLinkButton()
            ->setHref($this->getDeleteUri($object))
            ->setDataAttributes([
                'uid' => $object->getUid(),
                'toggle' => 'tooltip',
                'label' => $label,
            ])
            ->setClasses('btn-delete')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:delete'))
            ->setIcon($this->getIconFactory()->getIcon('actions-edit-delete', Icon::SIZE_SMALL))
            ->render();
    }

    /**
     * @param Content $object
     * @return string
     */
    protected function getDeleteUri(Content $object)
    {
        $additionalParameters = array(
            $this->getModuleLoader()->getParameterPrefix() => array(
                'controller' => 'Content',
                'action' => 'delete',
                'format' => 'json',
                'matches' => array(
                    'uid' => $object->getUid(),
                ),
            ),
        );
        return $this->getModuleLoader()->getModuleUrl($additionalParameters);
    }

}
