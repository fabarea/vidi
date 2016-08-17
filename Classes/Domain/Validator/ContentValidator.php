<?php
namespace Fab\Vidi\Domain\Validator;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Exception\MissingIdentifierException;

/**
 * Validate "content"
 */
class ContentValidator
{

    /**
     * Check whether $Content object is valid.
     *
     * @param Content $content
     * @throws \Exception
     * @return void
     */
    public function validate(Content $content)
    {

        // Security check.
        if ($content->getUid() <= 0) {
            throw new MissingIdentifierException('Missing identifier for Content Object', 1351605542);
        }
    }

}
