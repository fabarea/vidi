<?php
namespace Fab\Vidi\TypeConverter;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

/**
 * Convert a file uid into a File object.
 */
class CsvToArrayConverter extends AbstractTypeConverter
{

    /**
     * @var array<string>
     */
    protected $sourceTypes = array('string');

    /**
     * @var string
     */
    protected $targetType = 'array';

    /**
     * @var integer
     */
    protected $priority = 1;

    /**
     * Actually convert from $source to $targetType
     *
     * @param string $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return array
     * @throws \Exception
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     * @api
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = array(), PropertyMappingConfigurationInterface $configuration = NULL)
    {
        return GeneralUtility::trimExplode(',', $source, true);
    }

}