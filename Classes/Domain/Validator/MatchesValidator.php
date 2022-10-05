<?php

namespace Fab\Vidi\Domain\Validator;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use Fab\Vidi\Tca\Tca;

/**
 * Validate "matches" to be used to filter the repository.
 */
class MatchesValidator extends AbstractValidator
{
    /**
     * Check if $matches is valid. If it is not valid, throw an exception.
     *
     * @param mixed $matches
     * @return void
     */
    public function isValid($matches)
    {
        foreach ($matches as $fieldName => $value) {
            if (!Tca::table()->hasField($fieldName)) {
                $message = sprintf('Field "%s" is not allowed. Actually, it is not configured in the TCA.', $fieldName);
                $this->addError($message, 1380019718);
            }
        }
    }
}
