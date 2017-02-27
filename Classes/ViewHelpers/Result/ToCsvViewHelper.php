<?php
namespace Fab\Vidi\ViewHelpers\Result;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper for rendering a CSV export request.
 */
class ToCsvViewHelper extends AbstractToFormatViewHelper
{

    /**
     * Render a CSV export request.
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
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
     * @return void
     * @throws \Exception
     */
    protected function writeCsvFile(array $objects)
    {

        // Create a file pointer
        $output = fopen($this->exportFileNameAndPath, 'w');

        // Handle CSV header, get the first object and get the list of fields.
        /** @var \Fab\Vidi\Domain\Model\Content $object */
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
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
     */
    protected function sendCsvHttpHeaders()
    {

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
        $response = $this->templateVariableContainer->get('response');
        $response->setHeader('Content-Type', 'application/csv');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->exportFileNameAndPath) . '"');
        $response->setHeader('Content-Length', filesize($this->exportFileNameAndPath));
        $response->setHeader('Content-Description', 'File Transfer');

        $response->sendHeaders();
    }

}
