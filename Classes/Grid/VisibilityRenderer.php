<?php

namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Localization\LanguageService;
use Fab\Vidi\Domain\Model\Content;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class rendering visibility for the Grid.
 */
class VisibilityRenderer extends ColumnRendererAbstract
{
    /**
     * Render visibility for the Grid.
     *
     * @return string
     */
    public function render()
    {
        $output = '';
        $hiddenField = Tca::table()->getHiddenField();

        if ($hiddenField) {
            $spriteName = $this->object[$hiddenField] ? 'actions-edit-unhide' : 'actions-edit-hide';

            $label = $this->object[$hiddenField] ? 'unHide' : 'hide';

            $output = $this->makeLinkButton()
                ->setHref($this->getEditUri($this->object))
                ->setClasses('btn-visibility-toggle')
                ->setDataAttributes([
                    'toggle' => 'tooltip',
                ])
                ->setTitle($this->getLabelService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:' . $label))
                ->setIcon($this->getIconFactory()->getIcon($spriteName, Icon::SIZE_SMALL))
                ->render();
        }
        return $output;
    }

    /**
     * @param Content $object
     * @return string
     */
    protected function getEditUri(Content $object)
    {
        $hiddenField = Tca::table()->getHiddenField();

        $additionalParameters = array(
            $this->getModuleLoader()->getParameterPrefix() => [
                'controller' => 'Content',
                'action' => 'update',
                'format' => 'json',
                'fieldNameAndPath' => Tca::table()->getHiddenField(),
                'matches' => [
                    'uid' => $object->getUid(),
                ],
                'content' => [$hiddenField => (int)!$this->object[$hiddenField]],
            ],
        );

        return $this->getModuleLoader()->getModuleUrl($additionalParameters);
    }

    /**
     * @return LinkButton
     */
    protected function makeLinkButton()
    {
        return GeneralUtility::makeInstance(LinkButton::class);
    }


    /**
     * @return IconFactory
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * @return LanguageService
     */
    protected function getLabelService()
    {
        return $GLOBALS['LANG'];
    }
}
