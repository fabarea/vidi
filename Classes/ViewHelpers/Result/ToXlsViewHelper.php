<?php

namespace Fab\Vidi\ViewHelpers\Result;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Domain\Model\Content;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Service\SpreadSheetService;

/**
 * View helper for rendering a CSV export request.
 */
class ToXlsViewHelper extends AbstractToFormatViewHelper
{
    /**
     * Render a XLS export request.
     */
    public function render()
    {
        $objects = $this->templateVariableContainer->get('objects');

        // Make sure we have something to process...
        if (!empty($objects)) {
            // Initialization step.
            $this->initializeEnvironment($objects);
            $this->exportFileNameAndPath .= '.xls'; // add extension to the file.

            // Write the exported data to a CSV file.
            $this->writeXlsFile($objects);

            // We must generate a zip archive since there are files included.
            if ($this->hasCollectedFiles()) {
                $this->writeZipFile();
                $response = $this->getZipResponse();
            } else {
                $response = $this->getXlsResponse($this->exportFileNameAndPath);
            }

            GeneralUtility::rmdir($this->temporaryDirectory, true);

            $this->sendRepsonse($response);
        }
    }

    /**
     * Write the CSV file to a temporary location.
     *
     * @param array $objects
     * @return void
     */
    protected function writeXlsFile(array $objects)
    {
        /** @var SpreadSheetService $spreadSheet */
        $spreadSheet = GeneralUtility::makeInstance(SpreadSheetService::class);

        // Handle object header, get the first object and get the list of fields.
        /** @var Content $object */
        $object = reset($objects);
        $spreadSheet->addRow($object->toFields());

        $this->checkWhetherObjectMayIncludeFiles($object);

        foreach ($objects as $object) {
            if ($this->hasFileFields()) {
                $this->collectFiles($object);
            }

            // Make sure we have a flat array of values for the CSV purpose.
            $flattenValues = [];
            foreach ($object->toValues() as $fieldName => $value) {
                if (is_array($value)) {
                    $flattenValues[$fieldName] = implode(', ', $value);
                } else {
                    $flattenValues[$fieldName] = $value;
                }
            }

            $spreadSheet->addRow($flattenValues);
        }

        file_put_contents($this->exportFileNameAndPath, $spreadSheet->toString());
    }

    /**
     * @return Response
     */
    protected function getXlsResponse(string $fileNameAndPath)
    {
        return $this->getFileResponse($fileNameAndPath)->withHeader('Content-Type', 'application/vnd.ms-excel');
    }
}
