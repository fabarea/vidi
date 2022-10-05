<?php

namespace Fab\Vidi\Domain\Repository;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Factory class to server instances of Content repositories.
 */
class ContentRepositoryFactory implements SingletonInterface
{
    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * Returns a class instance of a repository.
     * If not data type is given, get the value from the module loader.
     *
     * @param string $dataType
     * @param string $sourceFieldName
     * @return ContentRepository
     */
    public static function getInstance($dataType = null, $sourceFieldName = '')
    {
        /** @var ModuleLoader $moduleLoader */
        if (is_null($dataType)) {
            // Try to get the data type from the module loader.
            $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);
            $dataType = $moduleLoader->getDataType();
        }

        // This should not happen.
        if (!$dataType) {
            throw new \RuntimeException('No data type given nor could be fetched by the module loader.', 1376118278);
        }

        if (empty(self::$instances[$dataType])) {
            $className = 'Fab\Vidi\Domain\Repository\ContentRepository';
            self::$instances[$dataType] = GeneralUtility::makeInstance($className, $dataType, $sourceFieldName);
        }

        /** @var ContentRepository $contentRepository */
        $contentRepository = self::$instances[$dataType];
        $contentRepository->setSourceFieldName($sourceFieldName);
        return $contentRepository;
    }
}
