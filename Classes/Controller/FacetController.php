<?php
namespace Fab\Vidi\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Fab\Vidi\Facet\FacetInterface;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller which handles actions related to "Facet" in a Vidi module.
 */
class FacetController extends ActionController
{

    /**
     * Suggest values according to a facet.
     * Output a json list of key / values.
     *
     * @param string $facet
     * @param string $searchTerm
     * @validate $facet Fab\Vidi\Domain\Validator\FacetValidator
     * @return string
     */
    public function autoSuggestAction($facet, $searchTerm)
    {

        $suggestions = $this->getFacetSuggestionService()->getSuggestions($facet);

        # Json header is not automatically sent in the BE...
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->sendHeaders();
        return json_encode($suggestions);
    }

    /**
     * Suggest values for all configured facets in the Grid.
     * Output a json list of key / values.
     *
     * @return string
     */
    public function autoSuggestsAction()
    {

        $suggestions = array();
        foreach (Tca::grid()->getFacets() as $facet) {
            /** @var FacetInterface $facet */
            $name = $facet->getName();
            $suggestions[$name] = $this->getFacetSuggestionService()->getSuggestions($name);
        }

        # Json header is not automatically sent in the BE...
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->sendHeaders();
        return json_encode($suggestions);
    }

    /**
     * @return \Fab\Vidi\Facet\FacetSuggestionService
     */
    protected function getFacetSuggestionService()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Facet\FacetSuggestionService');
    }

}
