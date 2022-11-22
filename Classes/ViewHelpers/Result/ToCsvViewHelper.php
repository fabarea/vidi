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

/**
 * View helper for rendering a CSV export request.
 */
class ToCsvViewHelper extends AbstractToFormatViewHelper
{
    /**
     * Render a CSV export request.
     *
     */
    public function render()
    {
        $objects = $this->templateVariableContainer->get('objects');

        // Make sure we have something to process...
        if (!empty($objects)) {
            // Initialization step.
            $this->initializeEnvironment($objects);
            $this->exportFileNameAndPath .= '.csv'; // add extension to the file.

            // Write the exported data to a CSV file.
            $this->writeCsvFile($objects);

            // We must generate a zip archive since there are files included.
            if ($this->hasCollectedFiles()) {
                $this->writeZipFile();
                $this->sendZipHttpHeaders();

                readfile($this->zipFileNameAndPath);
            } else {
                $this->sendCsvHttpHeaders();
                readfile($this->exportFileNameAndPath);
            }

            GeneralUtility::rmdir($this->temporaryDirectory, true);
        }
    }

    /**
     * Write the CSV file to a temporary location.
     *
     * @param array $objects
     */
    protected function writeCsvFile(array $objects)
    {
        // Create a file pointer
        $output = fopen($this->exportFileNameAndPath, 'w');

        // Handle CSV header, get the first object and get the list of fields.
        /** @var Content $object */
        $object = reset($objects);
        fputcsv($output, $object->toFields());
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
                    $flattenValues[$fieldName] = str_replace("\n", "\r", $value); // for Excel purpose.
                }
            }

            fputcsv($output, $flattenValues);
        }

        // close file handler
        fclose($output);
    }

    /**
     * @return void
     */
    protected function sendCsvHttpHeaders()
    {
        /** @var Response $response */
        $response = $this->templateVariableContainer->get('response');
        $response->withHeader('Content-Type', 'application/csv');
        $response->withHeader('Content-Disposition', 'attachment; filename="' . basename($this->exportFileNameAndPath) . '"');
        $response->withHeader('Content-Length', (string)filesize($this->exportFileNameAndPath));
        $response->withHeader('Content-Description', 'File Transfer');
    }
}
