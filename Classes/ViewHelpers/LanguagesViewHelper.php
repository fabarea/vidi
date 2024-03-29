<?php

namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Language\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which returns an array of available languages.
 */
class LanguagesViewHelper extends AbstractViewHelper
{
    /**
     * Returns an array of available languages.
     *
     * @return array
     */
    public function render()
    {
        $languages[0] = $this->getLanguageService()->getDefaultFlag();

        foreach ($this->getLanguageService()->getLanguages() as $language) {
            $languages[$language['uid']] = $language['flag'];
        }

        return $languages;
    }

    /**
     * @return LanguageService|object
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class);
    }
}
