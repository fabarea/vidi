<?php
namespace Fab\Vidi\Persistence;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Factory class related to Pager object.
 */
class PagerObjectFactory implements SingletonInterface
{

    /**
     * Gets a singleton instance of this class.
     *
     * @return \Fab\Vidi\Persistence\PagerObjectFactory|object
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Persistence\PagerObjectFactory::class);
    }

    /**
     * Returns a pager object.
     *
     * @return \Fab\Vidi\Persistence\Pager
     */
    public function getPager()
    {

        /** @var $pager \Fab\Vidi\Persistence\Pager */
        $pager = GeneralUtility::makeInstance(\Fab\Vidi\Persistence\Pager::class);

        // Set items per page
        if (GeneralUtility::_GET('length') !== null) {
            $limit = (int)GeneralUtility::_GET('length');
            $pager->setLimit($limit);
        }

        // Set offset
        $offset = 0;
        if (GeneralUtility::_GET('start') !== null) {
            $offset = (int)GeneralUtility::_GET('start');
        }
        $pager->setOffset($offset);

        // set page
        $page = 1;
        if ($pager->getLimit() > 0) {
            $page = round($pager->getOffset() / $pager->getLimit());
        }
        $pager->setPage($page);

        return $pager;
    }

}
