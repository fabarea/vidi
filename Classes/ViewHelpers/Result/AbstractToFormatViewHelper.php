<?php

namespace Fab\Vidi\ViewHelpers\Result;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Http\Response;
use Fab\Vidi\Tca\FieldType;
use Fab\Vidi\View\Grid\Rows;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Service\FileReferenceService;
use Fab\Vidi\Tca\Tca;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Abstract View helper for rendering an Export request.
 */
abstract class AbstractToFormatViewHelper extends AbstractViewHelper
{
    /**
     * Store fields of type "file".
     *
     * @var array
     */
    protected $fileTypeProperties = [];

    /**
     * @var File[]
     */
    protected $collectedFiles = [];

    /**
     * @var string
     */
    protected $exportFileNameAndPath;

    /**
     * @var string
     */
    protected $zipFileNameAndPath;

    /**
     * @var string
     */
    protected $temporaryDirectory;


    /**
     * Write the zip file to a temporary location.
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function writeZipFile()
    {
        $zip = new \ZipArchive();
        $zip->open($this->zipFileNameAndPath, \ZipArchive::CREATE);

        // Add the CSV content into the zipball.
        $zip->addFile($this->exportFileNameAndPath, basename($this->exportFileNameAndPath));

        // Add the files into the zipball.
        foreach ($this->collectedFiles as $file) {
            $zip->addFile($file->getForLocalProcessing(false), $file->getIdentifier());
        }

        $zip->close();
    }

    /**
     * Initialize some properties
     *
     * @param array $objects
     * @return void
     */
    protected function initializeEnvironment(array $objects)
    {
        /** @var Content $object */
        $object = reset($objects);

        $this->temporaryDirectory = Environment::getPublicPath() . '/typo3temp/' . uniqid() . '/';
        GeneralUtility::mkdir($this->temporaryDirectory);

        // Compute file name and path variable
        $this->exportFileNameAndPath = $this->temporaryDirectory . $object->getDataType() . '-' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']);

        // Compute file name and path variable for zip
        $zipFileName = $object->getDataType() . '-' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']) . '.zip';
        $this->zipFileNameAndPath = $this->temporaryDirectory . $zipFileName;
    }

    /**
     * Fetch the files given an object.
     *
     * @param Content $object
     * @return void
     */
    protected function collectFiles(Content $object)
    {
        foreach ($this->fileTypeProperties as $property) {
            $files = FileReferenceService::getInstance()->findReferencedBy($property, $object);
            foreach ($files as $file) {
                $this->collectedFiles[$file->getUid()] = $file;
            }
        }
    }

    /**
     * Tells whether the object has fields containing files.
     *
     * @return boolean
     */
    protected function hasCollectedFiles()
    {
        return !empty($this->collectedFiles);
    }

    /**
     * Tells whether the object has fields containing files.
     *
     * @return boolean
     */
    protected function hasFileFields()
    {
        return !empty($this->fileTypeProperties);
    }

    /**
     * Check whether the given object is meant to include files in some fields.
     *
     * @param Content $object
     */
    protected function checkWhetherObjectMayIncludeFiles(Content $object)
    {
        if (Tca::grid($object->getDataType())->areFilesIncludedInExport()) {
            foreach ($object->toFields() as $fieldName) {
                $fieldType = Tca::table($object->getDataType())->field($fieldName)->getType();

                if ($fieldType === FieldType::FILE) {
                    $this->fileTypeProperties[] = GeneralUtility::camelCaseToLowerCaseUnderscored($fieldName);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function sendZipHttpHeaders()
    {
        /** @var Response $response */
        $response = $this->templateVariableContainer->get('response');
        $response->withHeader('Pragma', 'public');
        $response->withHeader('Expires', '0');
        $response->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->withHeader('Content-Type', 'application/zip');
        $response->withHeader('Content-Disposition', 'attachment; filename="' . basename($this->zipFileNameAndPath) . '"');
        $response->withHeader('Content-Length', (string)filesize($this->zipFileNameAndPath));
        $response->withHeader('Content-Description', 'File Transfer');
        $response->withHeader('Content-Transfer-Encoding', 'binary');
    }

    /**
     * @return Rows|object
     */
    protected function getRowsView()
    {
        return GeneralUtility::makeInstance(Rows::class);
    }
}
