<?php

namespace Fab\Vidi\Service;

/*
 * This file is part of the Fab/Vidi. project.
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DataService
 */
class DataService implements SingletonInterface
{

    public function getRecord(string $tableName, array $demand = []): array
    {
        $queryBuilder = $this->getQueryBuilder($tableName);
        $queryBuilder
            ->select('*')
            ->from($tableName);

        $this->addDemandConstraints($demand, $queryBuilder);
        $record = $queryBuilder->execute()->fetchAssociative();
        return is_array($record)
            ? $record
            : [];
    }

    public function getRecords(string $tableName, array $demand = [], int $maxResult = 0, int $firstResult = 0): array
    {
        $queryBuilder = $this->getQueryBuilder($tableName);
        $queryBuilder
            ->select('*')
            ->from($tableName);

        $this->addDemandConstraints($demand, $queryBuilder);

        if ($maxResult) {
            $queryBuilder->setMaxResults($maxResult);
        }

        if ($firstResult) {
            $queryBuilder->setFirstResult($firstResult);
        }

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    public function count(string $tableName, array $demand = [], int $maxResult = 0, int $firstResult = 0): int
    {
        $queryBuilder = $this->getQueryBuilder($tableName);

        // We have to count "manually" if we have a limit or offset.
        if ($maxResult || $firstResult) {
            $records = $this->getRecords($tableName, $demand, $maxResult, $firstResult);
            $count = count($records);
        } else {
            $queryBuilder
                ->count('*')
                ->from($tableName);

            $this->addDemandConstraints($demand, $queryBuilder);
            $count = (int)$queryBuilder->execute()->fetchOne();
        }
        return $count;
    }

    public function insert(string $tableName, array $values): int
    {
        $connection = $this->getConnection($tableName);
        $connection->insert(
            $tableName,
            $values
        );
        return (int)$connection->lastInsertId();
    }

    public function update(string $tableName, array $values, array $identifiers): void
    {
        $connection = $this->getConnection($tableName);
        $connection->update(
            $tableName,
            $values,
            $identifiers
        );
    }

    public function delete(string $tableName, array $identifiers): void
    {
        $connection = $this->getConnection($tableName);
        $connection->delete(
            $tableName,
            $identifiers
        );
    }

    protected function addDemandConstraints(array $demand, $queryBuilder): void
    {
        $expressions = [];
        foreach ($demand as $fieldName => $value) {
            if (is_numeric($value)) {
                $expressions[] = $queryBuilder->expr()->eq(
                    $fieldName,
                    $value
                );
            } elseif (is_string($value)) {
                $expressions[] = $queryBuilder->expr()->eq(
                    $fieldName,
                    $queryBuilder->expr()->literal($value)
                );
            } elseif (is_array($value)) {
                $expressions[] = $queryBuilder->expr()->in(
                    $fieldName,
                    $value
                );
            }
        }
        foreach ($expressions as $expression) {
            $queryBuilder->andWhere($expression);
        }
    }

    protected function getConnection(string $tableName): Connection
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getConnectionForTable($tableName);
    }

    protected function getQueryBuilder(string $tableName): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($tableName);
    }
}
