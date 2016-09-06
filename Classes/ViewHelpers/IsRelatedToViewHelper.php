<?php
namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for telling whether a Content is related to another Content.
 * e.g a User belongs to a User Group.
 */
class IsRelatedToViewHelper extends AbstractViewHelper
{

    /**
     * Tells whether a Content is related to another content.
     * The $fieldName corresponds to the relational field name
     * between the first content object and the second.
     *
     * @param \Fab\Vidi\Domain\Model\Content $relatedContent
     * @return boolean
     */
    public function render($relatedContent)
    {

        $isChecked = false;

        // Only computes whether the object is checked if one row is beeing edited.
        $numberOfObjects = $this->templateVariableContainer->get('numberOfObjects');
        if ($numberOfObjects === 1) {

            /** @var \Fab\Vidi\Domain\Model\Content $content */
            $content = $this->templateVariableContainer->get('content');
            $fieldName = $this->templateVariableContainer->get('fieldName');

            // Build an array of user group uids
            $relatedContentsIdentifiers = array();

            /** @var \Fab\Vidi\Domain\Model\Content $contentObject */
            foreach ($content[$fieldName] as $contentObject) {
                $relatedContentsIdentifiers[] = $contentObject->getUid();
            }

            $isChecked = in_array($relatedContent->getUid(), $relatedContentsIdentifiers);
        }

        return $isChecked;
    }
}
