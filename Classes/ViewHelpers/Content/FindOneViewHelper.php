<?php

namespace Fab\Vidi\ViewHelpers\Content;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Domain\Repository\ContentRepositoryFactory;
use Fab\Vidi\Persistence\Matcher;
use Fab\Vidi\Resolver\FieldPathResolver;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FindOneViewHelper
 */
class FindOneViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('type', 'string', 'The content type', true, '');
        $this->registerArgument('matches', 'array', 'Key / value array to be used as filter. The key corresponds to a field name.', false, []);
        $this->registerArgument('identifier', 'int', 'The identifier of the object to be fetched.', false, 0);
        $this->registerArgument('argumentName', 'string', 'The parameter name where to retrieve the identifier', false, 'tx_vidifrontend_pi1|uid');
        $this->registerArgument('as', 'string', 'The alias object', false, 'object');
    }

    /**
     * @return string Rendered string
     * @api
     */
    public function render()
    {
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        // Fetch the object
        $matches = self::computeMatches($arguments);
        $matcher = self::getMatcher($arguments['type'], $matches);

        $contentRepository = ContentRepositoryFactory::getInstance($arguments['type']);
        $object = $contentRepository->findOneBy($matcher);

        $output = '';
        if ($object) {
            // Render children with "as" variable.
            $templateVariableContainer = $renderingContext->getTemplateVariableContainer();
            $templateVariableContainer->add($arguments['as'], $object);
            $output = $renderChildrenClosure();
            $templateVariableContainer->remove($arguments['as']);
        }

        return $output;
    }

    /**
     * @param array $arguments
     * @return array
     */
    protected static function computeMatches(array $arguments)
    {
        $matches = [];

        $argumentValue = self::getArgumentValue($arguments['argumentName']);
        if ($argumentValue > 0) {
            $matches['uid'] = $argumentValue;
        }

        if ($arguments['matches']) {
            $matches = $arguments['matches'];
        }

        if ($arguments['identifier'] > 0) {
            $matches['uid'] = $arguments['identifier'];
        }

        // We want a default value in any case.
        if (!$matches) {
            $matches['uid'] = 0;
        }
        return $matches;
    }

    /**
     * Returns a matcher object.
     *
     * @param string $dataType
     * @param array $matches
     * @return Matcher
     */
    protected static function getMatcher($dataType, array $matches = [])
    {
        /** @var $matcher Matcher */
        $matcher = GeneralUtility::makeInstance(Matcher::class, [], $dataType);

        foreach ($matches as $fieldNameAndPath => $value) {
            // CSV values should be considered as "in" operator in Query, otherwise "equals".
            $explodedValues = GeneralUtility::trimExplode(',', $value, true);

            // The matching value contains a "1,2" as example
            if (count($explodedValues) > 1) {
                $resolvedDataType = self::getFieldPathResolver()->getDataType($fieldNameAndPath, $dataType);
                $resolvedFieldName = self::getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $dataType);

                // "equals" if in presence of a relation.
                // "in" if not a relation.
                if (Tca::table($resolvedDataType)->field($resolvedFieldName)->hasRelation()) {
                    foreach ($explodedValues as $explodedValue) {
                        $matcher->equals($fieldNameAndPath, $explodedValue);
                    }
                } else {
                    $matcher->in($fieldNameAndPath, $explodedValues);
                }
            } else {
                $matcher->equals($fieldNameAndPath, $explodedValues[0]);
            }
        }

        return $matcher;
    }

    /**
     * @return FieldPathResolver
     */
    protected static function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance(FieldPathResolver::class);
    }

    /**
     * @param string $argumentName
     * @return int
     */
    protected static function getArgumentValue($argumentName)
    {
        $value = ''; // default value

        // Merge parameters
        $parameters = GeneralUtility::_GET();
        $post = GeneralUtility::_POST();
        ArrayUtility::mergeRecursiveWithOverrule($parameters, $post);

        // Traverse argument parts and retrieve value.
        $argumentParts = GeneralUtility::trimExplode('|', $argumentName);
        foreach ($argumentParts as $argumentPart) {
            if (isset($parameters[$argumentPart])) {
                $value = $parameters[$argumentPart];
                $parameters = $value;
            }
        }

        return (int)$value;
    }
}
