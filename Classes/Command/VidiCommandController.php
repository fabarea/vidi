<?php

namespace Fab\Vidi\Command;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Grid\GridAnalyserService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Symfony\Component\Console\Command\Command;
use Fab\Vidi\Tca\Tca;

/**
 * Command Controller which handles actions related to Vidi.
 */
class VidiCommandController extends Command
{

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Check TCA configuration for relations used in grid.')
        ->addOption(
        'table',
        'c',
        InputOption::VALUE_NONE,
        'The table name. If not defined check for every table.'
    );
    }

    /**
     * Executes the command for removing the lock file
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        foreach ($GLOBALS['TCA'] as $tableName => $TCA) {
            $table = $input->getOption('table');
            if ($table !== '' && $table !== $tableName) {
                continue;
            }

            $fields = Tca::grid($tableName)->getFields();
            if (!empty($fields)) {

                $relations = $this->getGridAnalyserService()->checkRelationForTable($tableName);
                if (!empty($relations)) {

                    $io->text('');
                    $io->text('--------------------------------------------------------------------');
                    $io->text('');
                    $io->text(sprintf('Relations for "%s"', $tableName));
                    $io->text('');
                    $io->text(implode("\n", $relations));
                }
            }
        }
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return GridAnalyserService|object
     */
    protected function getGridAnalyserService()
    {
        return GeneralUtility::makeInstance(GridAnalyserService::class);
    }
}
