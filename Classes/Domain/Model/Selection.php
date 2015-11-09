<?php
namespace Fab\Vidi\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Representation of a Selection
 */
class Selection extends AbstractEntity
{

    const VISIBILITY_EVERYONE = 0;
    const VISIBILITY_PRIVATE = 1;
    const VISIBILITY_ADMIN_ONLY = 2;

    /**
     * @var int
     */
    protected $visibility;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $speakingQuery;

    /**
     * @var int
     */
    protected $owner;

    /**
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getSpeakingQuery()
    {
        return $this->speakingQuery;
    }

    /**
     * @param string $speakingQuery
     * @return $this
     */
    public function setSpeakingQuery($speakingQuery)
    {
        $this->speakingQuery = $speakingQuery;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param int $owner
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

}
