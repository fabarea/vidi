<?php
namespace Fab\Vidi\DataHandler;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Abstract class for Data Handler in the context of Vidi.
 */
abstract class AbstractDataHandler implements DataHandlerInterface, SingletonInterface
{

    /**
     * @var array
     */
    protected $errorMessages;

    /**
     * Return error that have occurred while processing the data.
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

}
