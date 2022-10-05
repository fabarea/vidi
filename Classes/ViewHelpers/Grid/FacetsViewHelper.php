<?php

namespace Fab\Vidi\ViewHelpers\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Facet\FacetInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * View helper which returns the json serialization of the search fields.
 */
class FacetsViewHelper extends AbstractViewHelper
{
    /**
     * Returns the json serialization of the search fields.
     *
     * @return string
     */
    public function render()
    {
        $facets = [];
        foreach (Tca::grid()->getFacets() as $facet) {
            /** @var FacetInterface $facet */
            $name = $facet->getName();
            $facets[$name] = $facet->getLabel();
        }

        return json_encode($facets);
    }
}
