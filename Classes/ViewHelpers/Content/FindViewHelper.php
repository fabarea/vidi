<?php
namespace Fab\Vidi\ViewHelpers\Content;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Domain\Model\Selection;
use Fab\Vidi\Domain\Repository\ContentRepositoryFactory;
use Fab\Vidi\Domain\Repository\SelectionRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper which returns a list of records.
 */
class FindViewHelper extends AbstractContentViewHelper
{
    /**
     * @return void
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'orderings',
            'array',
            'Key / value array to be used for ordering. The key corresponds to a field name. The value can be "DESC" or "ASC".',
            false,
            [],
        );
        $this->registerArgument('limit', 'int', 'Limit the number of records being fetched.', false, 0);
        $this->registerArgument('offset', 'int', 'Where to start the list of records.', false, 0);
    }

    /**
     * Fetch and returns a list of content objects.
     *
     * @return array
     * @throws \BadMethodCallException
     */
    public function render()
    {
        $selectionIdentifier = (int) $this->arguments['selection'];

        if ($selectionIdentifier > 0) {
            /** @var SelectionRepository $selectionRepository */
            $selectionRepository = GeneralUtility::makeInstance(SelectionRepository::class);

            /** @var Selection $selection */
            $selection = $selectionRepository->findByUid($selectionIdentifier);
            $matches = json_decode($selection->getQuery(), true);
            $dataType = $selection->getDataType();
        } else {
            $dataType = $this->arguments['type'];
            if (!empty($this->arguments['dataType'])) {
                print 'Sorry to be so rude! There is something to change in the View Helper "v:find". Please replace attribute "dataType" by "type". This is a shorter syntax...';
                exit();
            }
            $matches = $this->replacesAliases($this->arguments['matches']);
        }

        $orderings = $this->replacesAliases($this->arguments['orderings']);
        $limit = $this->arguments['limit'];
        $offset = $this->arguments['offset'];
        $ignoreEnableFields = $this->arguments['ignoreEnableFields'];

        $querySignature = $this->getQuerySignature($dataType, $matches, $orderings, $limit, $offset);

        $resultSet = $this->getResultSetStorage()->get($querySignature);
        if (!$resultSet) {
            $matcher = $this->getMatcher($dataType, $matches);
            $orderings = $this->getOrder($dataType, $orderings);

            $this->emitPostProcessLimitSignal($dataType, $limit);
            $this->emitPostProcessOffsetSignal($dataType, $offset);

            $contentRepository = ContentRepositoryFactory::getInstance($dataType);
            $contentRepository->setDefaultQuerySettings($this->getDefaultQuerySettings($ignoreEnableFields));

            $resultSet = $contentRepository->findBy($matcher, $orderings, $limit, $offset);
            $this->getResultSetStorage()->set($querySignature, $resultSet); // store the result set for performance sake.
        }

        return $resultSet;
    }
}
