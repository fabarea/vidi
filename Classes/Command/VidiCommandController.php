<?php
namespace Fab\Vidi\Command;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use Fab\Vidi\Tca\Tca;

/**
 * Command Controller which handles actions related to Vidi.
 */
class VidiCommandController extends CommandController
{

    /**
     * Check TCA configuration for relations used in grid.
     *
     * @param string $table the table name. If not defined check for every table.
     * @return void
     */
    public function analyseRelationsCommand($table = '')
    {

        foreach ($GLOBALS['TCA'] as $tableName => $TCA) {

            if ($table != '' && $table !== $tableName) {
                continue;
            }

            $fields = Tca::grid($tableName)->getFields();
            if (!empty($fields)) {

                $relations = $this->getGridAnalyserService()->checkRelationForTable($tableName);
                if (!empty($relations)) {

                    $this->outputLine();
                    $this->outputLine('--------------------------------------------------------------------');
                    $this->outputLine();
                    $this->outputLine(sprintf('Relations for "%s"', $tableName));
                    $this->outputLine();
                    $this->outputLine(implode("\n", $relations));
                }
            }
        }
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Grid\GridAnalyserService
     */
    protected function getGridAnalyserService()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Grid\GridAnalyserService');
    }
}
