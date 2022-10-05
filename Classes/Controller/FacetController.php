<?php

namespace Fab\Vidi\Controller;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Facet\FacetInterface;
use Fab\Vidi\Facet\FacetSuggestionService;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
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
     * @Validate("Fab\Vidi\Domain\Validator\FacetValidator", param="facet")
     */
    public function autoSuggestAction($facet, $searchTerm)
    {
        $suggestions = $this->getFacetSuggestionService()->getSuggestions($facet);


        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($suggestions)));
    }

    /**
     * Suggest values for all configured facets in the Grid.
     * Output a json list of key / values.
     */
    public function autoSuggestsAction()
    {
        $suggestions = [];
        foreach (Tca::grid()->getFacets() as $facet) {
            /** @var FacetInterface $facet */
            $name = $facet->getName();
            $suggestions[$name] = $this->getFacetSuggestionService()->getSuggestions($name);
        }

        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($suggestions)));
    }

    /**
     * @return FacetSuggestionService|object
     */
    protected function getFacetSuggestionService()
    {
        return GeneralUtility::makeInstance(FacetSuggestionService::class);
    }
}
