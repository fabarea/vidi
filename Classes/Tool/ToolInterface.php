<?php
namespace Fab\Vidi\Tool;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Interface for checking.
 */
interface ToolInterface
{

    /**
     * Display the title of the tool on the welcome screen.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Display the description of the tool on the welcome screen.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Do the job.
     *
     * @param array $arguments
     * @return string
     */
    public function work(array $arguments = array());

    /**
     * Tell whether the tools should be displayed.
     *
     * @return bool
     */
    public function isShown();

}
