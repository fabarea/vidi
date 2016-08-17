<?php
namespace Fab\Vidi\Facet;

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

use Fab\Vidi\Persistence\Matcher;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class for configuring a custom Facet item.
 */
class StandardFacet implements FacetInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array
     */
    protected $suggestions = array();

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var bool
     */
    protected $canModifyMatcher = false;

    /**
     * Constructor of a Generic Facet in Vidi.
     *
     * @param string $name
     * @param string $label
     * @param array $suggestions
     */
    public function __construct($name, $label = '', array $suggestions = array())
    {
        $this->name = $name;
        if (empty($label)) {
            $label = $this->name;
        }
        $this->label = $label;
        $this->suggestions = $suggestions;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if ($this->label === $this->name) {
            $label = Tca::table($this->dataType)->field($this->getName())->getLabel();
        } else {
            $label = LocalizationUtility::translate($this->label, '');
            if (empty($label)) {
                $label = $this->label;
            }
        }

        return $label;
    }

    /**
     * @return array
     */
    public function getSuggestions()
    {

        $values = array();
        foreach ($this->suggestions as $key => $label) {
            $localizedLabel = $this->getLanguageService()->sL($label);
            if (!empty($localizedLabel)) {
                $label = $localizedLabel;
            }

            $values[] = [$key => $label];
        }

        return $values;
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return bool
     */
    public function hasSuggestions()
    {
        return !empty($this->suggestions);
    }

    /**
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return bool
     */
    public function canModifyMatcher()
    {
        return $this->canModifyMatcher;
    }

    /**
     * @param Matcher $matcher
     * @param $value
     * @return Matcher
     */
    public function modifyMatcher(Matcher $matcher, $value)
    {
        return $matcher;
    }

    /**
     * Magic method implementation for retrieving state.
     *
     * @param array $states
     * @return StandardFacet
     */
    static public function __set_state($states)
    {
        return new StandardFacet($states['name'], $states['label'], $states['suggestions']);
    }

}
