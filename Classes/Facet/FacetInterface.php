<?php
namespace Fab\Vidi\Facet;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Persistence\Matcher;

/**
 * Interface dealing with Facet for the Visual Search bar.
 */
interface FacetInterface
{

    /**
     * Return the "key" of the facet.
     *
     * @return string
     */
    public function getName();

    /**
     * Return the "label" of the facet.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Return possible "suggestions" of the facet.
     *
     * @return array
     */
    public function getSuggestions();

    /**
     * Tell whether the Facet has suggestion or not.
     *
     * @return bool
     */
    public function hasSuggestions();

    /**
     * Set the data type.
     *
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType);

    /**
     * @return bool
     */
    public function canModifyMatcher();

    /**
     * @param Matcher $matcher
     * @param $value
     * @return Matcher
     */
    public function modifyMatcher(Matcher $matcher, $value);

}
