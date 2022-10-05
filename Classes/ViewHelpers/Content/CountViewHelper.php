<?php

namespace Fab\Vidi\ViewHelpers\Content;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Repository\ContentRepositoryFactory;

/**
 * View helper which counts a result set.
 */
class CountViewHelper extends AbstractContentViewHelper
{
    /**
     * Count a result set.
     *
     * @return int
     */
    public function render()
    {
        if (!empty($this->arguments['dataType'])) {
            print 'Sorry to be so rude! There is something to change in the View Helper "v:find". Please replace attribute "dataType" by "type". This is a shorter syntax...';
            exit();
        }
        $dataType = $this->arguments['type'];
        $matches = $this->replacesAliases($this->arguments['matches']);
        $ignoreEnableFields = $this->arguments['ignoreEnableFields'];

        $matcher = $this->getMatcher($dataType, $matches);

        $contentRepository = ContentRepositoryFactory::getInstance($dataType);
        $contentRepository->setDefaultQuerySettings($this->getDefaultQuerySettings($ignoreEnableFields));

        $numberOfObjects = ContentRepositoryFactory::getInstance($dataType)->countBy($matcher);
        return $numberOfObjects;
    }
}
