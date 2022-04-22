<?php
namespace Fab\Vidi\Domain\Validator;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Language\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validate "language"
 */
class LanguageValidator
{

    /**
     * Check whether the $language is valid.
     *
     * @param int $language
     * @throws \Exception
     * @return void
     */
    public function validate($language)
    {

        if (!$this->getLanguageService()->languageExists((int)$language)) {
            throw new \Exception('The language "' . $language . '" does not exist', 1351605542);
        }
    }

    /**
     * @return LanguageService|object
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class);
    }

}
