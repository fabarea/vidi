<?php

namespace Fab\Vidi\View\Check;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\View\AbstractComponentView;
use Fab\Vidi\Tca\Tca;

/**
 * Class RelationsCheck
 */
class RelationsCheck extends AbstractComponentView
{
    /**
     * @var array
     */
    protected $invalidFields = [];

    /**
     * Renders a button for uploading assets.
     *
     * @return string
     */
    public function render()
    {
        $result = '';

        // Check whether storage is configured or not.
        if (!$this->isTcaValid()) {
            $result .= $this->formatMessageTcaIsNotValid();
        }

        return $result;
    }

    /**
     * Format a message whenever the storage is offline.
     *
     * @return string
     */
    protected function formatMessageTcaIsNotValid()
    {
        $result = <<< EOF
			<div class="-warning alert alert-warning">
				<div class="alert-title">
					Grid may have trouble to render because of wrong / missing TCA.
				</div>
				<div class="alert-message">
					TCA which describes the relations within "{$this->getModuleLoader()->getDataType()}" is incorrect.
					When dealing with MM relations, Vidi requires to have a TCA in both direction to work properly.

					You could try the following fix:

					{$this->formatMessageHelperText()}
				</div>
			</div>
EOF;
        return $result;
    }

    /**
     * Check relations of current data type in the Grid.
     *
     * @return string
     */
    protected function formatMessageHelperText()
    {
        $helperText = '';
        foreach ($this->invalidFields as $invalidField) {
            $helperText .= <<<EOF
				<br />
				In file EXT:my_ext/Configuration/TCA/{$this->getModuleLoader()->getDataType()}.php
<pre>

# Solution 1: remove field "{$invalidField}" from the Grid.
\$GLOBALS['TCA']['{$this->getModuleLoader()->getDataType()}']['grid']['excluded_fields'] => '$invalidField',

# Solution 2: configure field "{$invalidField}".
\$tca = array(
	'columns' => array(
		'CHANGE_ME' => array(
			'config' => array(
				'type' => 'select',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 9999,
				'autoSizeMax' => 30,
				'multiple' => 0,
				'foreign_table' => '{$this->getModuleLoader()->getDataType()}',
				'MM' => 'CHANGE_ME',
				'MM_opposite_field' => '{$invalidField}',
			),
		),
	),
);

# Those lines are only in case you need to override an existing TCA, not in your control.
if (!empty(\$GLOBALS['TCA']['{$invalidField}'])) {
	return \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule(\$GLOBALS['TCA']['{$invalidField}'], \$tca);
}

</pre>
EOF;
        }
        return $helperText;
    }

    /**
     * Check relations of current data type in the Grid.
     *
     * @return boolean
     */
    protected function isTcaValid()
    {
        $dataType = $this->getModuleLoader()->getDataType();
        $table = Tca::table($dataType);

        foreach (Tca::grid($dataType)->getFields() as $fieldName => $configuration) {
            if ($table->hasField($fieldName) && $table->field($fieldName)->hasMany()) {
                if ($table->field($fieldName)->hasRelationManyToMany()) {
                    $foreignTable = $table->field($fieldName)->getForeignTable();
                    $manyToManyTable = $table->field($fieldName)->getManyToManyTable();
                    $foreignField = $table->field($fieldName)->getForeignField();

                    if (!$foreignField) {
                        $this->invalidFields[] = $fieldName;
                    } elseif (!$foreignTable) {
                        $this->invalidFields[] = $fieldName;
                    } elseif (!$manyToManyTable) {
                        $this->invalidFields[] = $fieldName;
                    }
                }
            }
        }

        return empty($this->invalidFields);
    }
}
