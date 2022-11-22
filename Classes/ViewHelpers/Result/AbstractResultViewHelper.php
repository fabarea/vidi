<?php

namespace Fab\Vidi\ViewHelpers\Result;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

abstract class AbstractResultViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    protected function sendRepsonse(Response $response)
    {
        throw new PropagateResponseException($response, 1669044846);
    }

    /**
     * @param string $fileNameAndPath
     * @return Response
     */
    protected function getFileResponse($fileNameAndPath)
    {
        $streamFactory = GeneralUtility::makeInstance(StreamFactoryInterface::class);

        return $this->getResponse(basename($fileNameAndPath))
            ->withHeader('Content-Length', (string)filesize($fileNameAndPath))
            ->withBody($streamFactory->createStreamFromFile($fileNameAndPath));
    }

    /**
     * @param string $filename
     * @return Response
     */
    protected function getResponse($filename)
    {
        return $this->templateVariableContainer->get('response')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Cache-Control', 'public, must-revalidate');
    }
}
