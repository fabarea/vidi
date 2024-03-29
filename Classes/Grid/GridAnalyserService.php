<?php

namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tca\Tca;

/**
 * Class for analysing the Grid, e.g. the relations
 */
class GridAnalyserService
{
    /**
     * Check relation for table.
     *
     * @param $tableName
     * @return array
     */
    public function checkRelationForTable($tableName)
    {
        $relations = [];
        $table = Tca::table($tableName);

        $missingOppositionRelationMessage = <<<EOF

  WARNING! Could not define relation precisely. This is not necessarily a problem
  if the opposite relation is not required in a Grid. But consider adding the opposite
  TCA configuration if so.
EOF;

        foreach (Tca::grid($tableName)->getFields() as $fieldName => $configuration) {
            if ($table->hasField($fieldName)) {
                if ($table->field($fieldName)->hasMany()) {
                    if ($table->field($fieldName)->hasRelationWithCommaSeparatedValues()) {
                        $_relations = $this->checkRelationOf($tableName, $fieldName, 'comma separated values');
                        $relations = array_merge($relations, $_relations);
                    } elseif ($table->field($fieldName)->hasRelationManyToMany()) {
                        $_relations = $this->checkRelationManyToMany($tableName, $fieldName);
                        $relations = array_merge($relations, $_relations);
                    } elseif ($table->field($fieldName)->hasRelationOneToMany()) {
                        $_relations = $this->checkRelationOf($tableName, $fieldName, 'one-to-many');
                        $relations = array_merge($relations, $_relations);
                    } else {
                        $relations[] = sprintf('* field: "%s", relation: ?-to-many%s', $fieldName, $missingOppositionRelationMessage);
                    }
                    $relations[] = '';
                } elseif ($table->field($fieldName)->hasOne()) {
                    if ($table->field($fieldName)->hasRelationOneToOne()) {
                        $relations[] = sprintf('* one-to-one "%s"', $fieldName);
                    } elseif ($table->field($fieldName)->hasRelationManyToOne()) {
                        $_relations = $this->checkRelationOf($tableName, $fieldName, 'many-to-one');
                        $relations = array_merge($relations, $_relations);
                    } else {
                        $relations[] = sprintf('* field: "%s", relation: ?-to-one%s', $fieldName, $missingOppositionRelationMessage);
                    }
                    $relations[] = '';
                }
            }
        }
        return $relations;
    }

    /**
     * Convenience method for printing out relation many-to-many.
     *
     * @param string $tableName
     * @param string $fieldName
     * @return array
     */
    protected function checkRelationManyToMany($tableName, $fieldName)
    {
        $output = [];

        $table = Tca::table($tableName);
        $output[] = sprintf('* field: "%s", relation: many-to-many', $fieldName);

        $foreignTable = $table->field($fieldName)->getForeignTable();
        $manyToManyTable = $table->field($fieldName)->getManyToManyTable();
        $foreignField = $table->field($fieldName)->getForeignField();

        if (!$foreignField) {
            $output[] = sprintf('  ERROR! Can not found foreign field for "%s". Perhaps missing opposite configuration?', $fieldName);
        } elseif (!$foreignTable) {
            $output[] = sprintf('  ERROR! Can not found foreign table for "%s". Perhaps missing opposite configuration?', $fieldName);
        } elseif (!$manyToManyTable) {
            $output[] = sprintf('  ERROR! Can not found relation table (MM) for "%s". Perhaps missing opposite configuration?', $fieldName);
        } else {
            $output[] = sprintf('  %s.%s <--> %s <--> %s.%s', $tableName, $fieldName, $manyToManyTable, $foreignTable, $foreignField);
        }

        $output[] = '';
        return $output;
    }

    /**
     * Convenience method for printing out relation.
     *
     * @param string $tableName
     * @param string $fieldName
     * @param string $relationType
     * @return array
     */
    protected function checkRelationOf($tableName, $fieldName, $relationType)
    {
        $output = [];

        $table = Tca::table($tableName);
        $output[] = sprintf('* field: "%s", relation: %s', $fieldName, $relationType);

        $foreignTable = $table->field($fieldName)->getForeignTable();
        $foreignField = $table->field($fieldName)->getForeignField();
        $output[] = sprintf('  %s.%s <--> %s.%s', $tableName, $fieldName, $foreignTable, $foreignField);
        $output[] = '';

        return $output;
    }
}
