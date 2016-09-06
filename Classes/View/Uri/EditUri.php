<?php
namespace Fab\Vidi\View\Uri;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\View\AbstractComponentView;
use Fab\Vidi\Domain\Model\Content;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * View which renders a "edit" button to be placed in the grid.
 */
class EditUri extends AbstractComponentView
{

    /**
     * Renders a "edit" button to be placed in the grid.
     *
     * @param Content $object
     * @return string
     */
    public function render(Content $object = null)
    {
        $uri = BackendUtility::getModuleUrl(
            'record_edit',
            array(
                $this->getEditParameterName($object) => 'edit',
                'returnUrl' => $this->getModuleLoader()->getModuleUrl()
            )
        );
        return $uri;
    }

    /**
     * @param Content $object
     * @return string
     */
    protected function getEditParameterName(Content $object)
    {
        return sprintf(
            'edit[%s][%s]',
            $object->getDataType(),
            $object->getUid()
        );
    }

}
