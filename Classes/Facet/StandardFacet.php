<?php
namespace Fab\Vidi\Facet;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Persistence\Matcher;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    protected $suggestions = [];

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
            try {
                $label = LocalizationUtility::translate($this->label, '');
            } catch (\InvalidArgumentException $e) {
            }
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

        $values = [];
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

        /** @var \TYPO3\CMS\Lang\LanguageService $langService */
        $langService = $GLOBALS['LANG'];
        if (!$langService) {
            $langService = GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class);
            $langService->init('en');
        }

        return $langService;
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
