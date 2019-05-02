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
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class CompositeFacet
 */
class CompositeFacet implements FacetInterface
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
    protected $suggestions = [
        '0' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active.0',
    ];

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var bool
     */
    protected $canModifyMatcher = true;

    /**
     * Constructor of a Generic Facet in Vidi.
     *
     * @param string $name
     * @param string $label
     * @param array $suggestions
     * @param array $configuration
     */
    public function __construct($name, $label = '', array $suggestions = [], $configuration = [])
    {
        $this->name = $name;
        if (empty($label)) {
            $label = $this->name;
        }
        $this->label = $label;
        if ($suggestions) {
            $this->suggestions = $suggestions;
        }
        if ($configuration) {
            $this->configuration = $configuration;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
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
    public function getSuggestions(): array
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
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        /** @var LanguageService $langService */
        $langService = $GLOBALS['LANG'];
        if (!$langService) {
            $langService = GeneralUtility::makeInstance(LanguageService::class);
            $langService->init('en');
        }

        return $langService;
    }

    /**
     * @return bool
     */
    public function hasSuggestions(): bool
    {
        return !empty($this->suggestions);
    }

    /**
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType): self
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return bool
     */
    public function canModifyMatcher(): bool
    {
        return $this->canModifyMatcher;
    }

    /**
     * @param Matcher $matcher
     * @param $value
     * @return Matcher
     */
    public function modifyMatcher(Matcher $matcher, $value): Matcher
    {
        foreach ($this->configuration as $fieldNameAndPath => $operand) {
            if ($operand === '*') {
                $matcher->notEquals($fieldNameAndPath, '');
            } else {
                $matcher->equals($fieldNameAndPath, $operand);
            }
        }
        return $matcher;
    }

}
