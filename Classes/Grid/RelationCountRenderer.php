<?php

namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Fab\Vidi\Tca\Tca;

/**
 * Class rendering relation
 */
class RelationCountRenderer extends ColumnRendererAbstract
{
    /**
     * Render a representation of the relation on the GUI.
     *
     * @return string
     */
    public function render()
    {
        $output = '';
        if ($this->isBackendMode()) {
            $output = $this->renderForBackend();
        }

        return $output;
    }

    /**
     * @return string
     */
    protected function renderForBackend()
    {
        $numberOfObjects = count($this->object[$this->fieldName]);

        if ($numberOfObjects > 1) {
            $label = 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:items';
            if (isset($this->gridRendererConfiguration['labelPlural'])) {
                $label = $this->gridRendererConfiguration['labelPlural'];
            }
        } else {
            $label = 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:item';
            if (isset($this->gridRendererConfiguration['labelSingular'])) {
                $label = $this->gridRendererConfiguration['labelSingular'];
            }
        }

        $template = '<a href="%s&returnUrl=%s&search=%s&query=%s:%s">%s %s<span class="invisible" style="padding-left: 5px">%s</span></a>';

        $foreignField = Tca::table($this->object)->field($this->fieldName)->getForeignField();
        $search = json_encode(array(array($foreignField => $this->object->getUid())));

        $moduleTarget = empty($this->gridRendererConfiguration['targetModule']) ? '' : $this->gridRendererConfiguration['targetModule'];
        return sprintf(
            $template,
            BackendUtility::getModuleUrl($moduleTarget),
            rawurlencode(BackendUtility::getModuleUrl($this->gridRendererConfiguration['sourceModule'])),
            rawurlencode($search),
            rawurlencode($foreignField),
            rawurlencode($this->object->getUid()),
            htmlspecialchars($numberOfObjects),
            htmlspecialchars(LocalizationUtility::translate($label, '')),
            $this->getIconFactory()->getIcon('extensions-vidi-go', Icon::SIZE_SMALL)
        );
    }

    /**
     * Returns whether the current mode is Frontend
     *
     * @return bool
     */
    protected function isBackendMode()
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }
}
