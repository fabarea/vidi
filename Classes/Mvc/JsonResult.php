<?php

namespace Fab\Vidi\Mvc;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Representation of a Result to be passed to the View.
 */
class JsonResult
{
    /**
     * @var int
     */
    protected $numberOfObjects = 0;

    /**
     * @var int
     */
    protected $numberOfProcessedObjects = 0;

    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @var array
     */
    protected $processedObject = [];

    /**
     * @var array
     */
    protected $row = null;

    /**
     * @return $this
     */
    public function incrementNumberOfProcessedObjects()
    {
        $this->numberOfProcessedObjects++;
    }

    /**
     * @param string $errorMessages
     * @return $this
     */
    public function addErrorMessages($errorMessages)
    {
        if (!empty($errorMessages)) {
            $this->errorMessages[] = $errorMessages;
        } else {
            $this->incrementNumberOfProcessedObjects();
        }
        return $this;
    }

    /**
     * @param array $errorMessages
     * @return $this
     */
    public function setErrorMessages($errorMessages)
    {
        $this->errorMessages = $errorMessages;
        return $this;
    }

    /**
     * @param array $processedObject
     * @return $this
     */
    public function setProcessedObject($processedObject)
    {
        $this->processedObject = $processedObject;
        return $this;
    }

    /**
     * @return $this
     */
    public function hasErrors()
    {
        return !empty($this->errorMessages);
    }

    /**
     * @param mixed $numberOfObjects
     * @return $this
     */
    public function setNumberOfObjects($numberOfObjects)
    {
        $this->numberOfObjects = $numberOfObjects;
        return $this;
    }

    /**
     * @param mixed $row
     * @return $this
     */
    public function setRow(array $row)
    {
        $this->row = $row;
        return $this;
    }

    /**
     * Convert $this to array
     *
     * @return array
     */
    public function toArray()
    {
        $arrayValues = array(
            'numberOfObjects' => $this->numberOfObjects,
            'numberOfProcessedObjects' => $this->numberOfProcessedObjects,
            'hasErrors' => $this->hasErrors(),
            'errorMessages' => $this->errorMessages,
            'row' => $this->row,
        );

        // Only feed key processedObject if it has values.
        if (!empty($this->processedObject)) {
            $arrayValues['processedObject'] = $this->processedObject;
        }

        return $arrayValues;
    }
}
