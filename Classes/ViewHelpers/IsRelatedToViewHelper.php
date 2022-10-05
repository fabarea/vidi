<?php

namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Model\Content;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for telling whether a Content is related to another Content.
 * e.g a User belongs to a User Group.
 */
class IsRelatedToViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('relatedContent', Content::class, 'The related content', true);
    }

    /**
     * Tells whether a Content is related to another content.
     * The $fieldName corresponds to the relational field name
     * between the first content object and the second.
     *
     * @return boolean
     */
    public function render()
    {
        /** @var Content $relatedContent */
        $relatedContent = $this->arguments['relatedContent'];

        $isChecked = false;

        // Only computes whether the object is checked if one row is beeing edited.
        $numberOfObjects = $this->templateVariableContainer->get('numberOfObjects');
        if ($numberOfObjects === 1) {
            /** @var Content $content */
            $content = $this->templateVariableContainer->get('content');
            $fieldName = $this->templateVariableContainer->get('fieldName');

            // Build an array of user group uids
            $relatedContentsIdentifiers = [];

            /** @var Content $contentObject */
            foreach ($content[$fieldName] as $contentObject) {
                $relatedContentsIdentifiers[] = $contentObject->getUid();
            }

            $isChecked = in_array($relatedContent->getUid(), $relatedContentsIdentifiers, true);
        }

        return $isChecked;
    }
}
