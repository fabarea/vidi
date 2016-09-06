<?php
namespace Fab\Vidi\Persistence;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class for storing result set to improve performance.
 */
class ResultSetStorage implements SingletonInterface
{

    /**
     * @var array
     */
    protected $resultSets = [];

    /**
     * @param string $querySignature
     * @return array
     */
    public function get($querySignature)
    {
        $resultSet = null;
        if (isset($this->resultSets[$querySignature])) {
            $resultSet = $this->resultSets[$querySignature];
        }
        return $resultSet;
    }

    /**
     * @param $querySignature
     * @param array $resultSet
     * @internal param array $resultSets
     */
    public function set($querySignature, array $resultSet)
    {
        $this->resultSets[$querySignature] = $resultSet;
    }

}
