<?php

namespace Fab\Vidi\DataHandler;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Model\Content;

/**
 * Interface dealing with Data Handling.
 */
interface DataHandlerInterface
{
    /**
     * Process Content with action "update".
     *
     * @param Content $content
     * @return bool
     */
    public function processUpdate(Content $content);

    /**
     * Process Content with action "remove".
     *
     * @param Content $content
     * @return bool
     */
    public function processRemove(Content $content);

    /**
     * Process Content with action "copy".
     *
     * @param Content $content
     * @param string $target
     * @return bool
     */
    public function processCopy(Content $content, $target);

    /**
     * Process Content with action "move".
     *
     * @param Content $content
     * @param mixed $target
     * @return bool
     */
    public function processMove(Content $content, $target);

    /**
     * Process Content with action "localize".
     *
     * @param Content $content
     * @param int $language
     * @return bool
     */
    public function processLocalize(Content $content, $language);

    /**
     * Return error that have occurred while processing the data.
     *
     * @return array
     */
    public function getErrorMessages();
}
