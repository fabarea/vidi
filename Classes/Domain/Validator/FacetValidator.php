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
 * Validate "facet" to be used in the repository.
 */
class FacetValidator extends AbstractValidator
{
    /**
     * Check if $facet is valid. If it is not valid, throw an exception.
     *
     * @param mixed $facet
     * @return void
     */
    public function isValid($facet)
    {
        if (!Tca::grid()->hasFacet($facet)) {
            $message = sprintf('Facet "%s" is not allowed. Actually, it was not configured to be displayed in the grid.', $facet);
            $this->addError($message, 1380019719);
        }
    }
}
