<?php

namespace Fab\Vidi\Service;

/*
 * This file is part of the Fab/Vidi. project.
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DataService
 */
class DataService implements SingletonInterface
{

    /**
     * @param string $tableName
     * @param array $demand
     * @return array
     */
    public function getRecord(string $tableName, array $demand = []): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getQueryBuilder($tableName);
        $queryBuilder
            ->select('*')
            ->from($tableName);

        $this->addDemandConstraints($demand, $queryBuilder);
        $record = $queryBuilder->execute()->fetch();
        return is_array($record)
            ? $record
            : [];
    }

    /**
     * @param string $tableName
     * @param array $demand
     * @return array
     */
    public function getRecords(string $tableName, array $demand = []): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getQueryBuilder($tableName);
        $queryBuilder
            ->select('*')
            ->from($tableName);

        $this->addDemandConstraints($demand, $queryBuilder);

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * @param string $tableName
     * @param array $demand
     * @return int
     */
    public function count(string $tableName, array $demand = []): int
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getQueryBuilder($tableName);
        $queryBuilder
            ->count('*')
            ->from($tableName);

        $this->addDemandConstraints($demand, $queryBuilder);

        return (int)$queryBuilder->execute()->fetchColumn(0);
    }

    /**
     * @param string $tableName
     * @param array $values
     * @return int
     */
    public function insert(string $tableName, array $values): int
    {
        $connection = $this->getConnection($tableName);
        $connection->insert(
            $tableName,
            $values
        );
        return (int)$connection->lastInsertId();
    }

    /**
     * @param string $tableName
     * @param array $values
     * @param array $identifiers
     * @return void
     */
    public function update(string $tableName, array $values, array $identifiers): void
    {
        $connection = $this->getConnection($tableName);
        $connection->update(
            $tableName,
            $values,
            $identifiers
        );
    }

    /**
     * @param string $tableName
     * @param array $identifiers
     */
    public function delete(string $tableName, array $identifiers): void
    {
        $connection = $this->getConnection($tableName);
        $connection->delete(
            $tableName,
            $identifiers
        );
    }

    /**
     * @param array $demand
     * @param QueryBuilder $queryBuilder
     * @return void
     */
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

    /**
     * @return object|DeletedRestriction
     */
    protected function getDeletedRestriction(): DeletedRestriction
    {
        return GeneralUtility::makeInstance(DeletedRestriction::class);
    }

    /**
     * @return object|HiddenRestriction
     */
    protected function getHiddenRestriction(): HiddenRestriction
    {
        return GeneralUtility::makeInstance(HiddenRestriction::class);
    }

    /**
     * @param string $tableName
     * @return object|Connection
     */
    protected function getConnection($tableName): Connection
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getConnectionForTable($tableName);
    }

    /**
     * @param string $tableName
     * @return object|QueryBuilder
     */
    protected function getQueryBuilder($tableName): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($tableName);
    }
}
