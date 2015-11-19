<?php
namespace Fab\Vidi\View\Check;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Fab\Vidi\View\AbstractComponentView;
use Fab\Vidi\Tca\Tca;

/**
 * View which renders check.
 */
class RelationsCheck extends AbstractComponentView
{

    /**
     * @var array
     */
    protected $invalidFields = array();

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
			<div class="typo3-message message-warning">
				<div class="message-header">
					Grid may have trouble to render because of wrong / missing TCA.
				</div>
				<div class="message-body">
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

        // Hunt for System Fields which has been removed
        // @todo remove me in 0.6 + 2 versions
        $systemFields = array();
        foreach (Tca::grid($dataType)->getFields() as $fieldName => $configuration) {
            if (Tca::grid($dataType)->isSystem($fieldName) && !Tca::grid($dataType)->hasRenderers($fieldName)) {
                $systemFields[] = $fieldName;
            }
        }

        if (!empty($systemFields)) {
            print 'You are using some old Grid configuration which requires to be changed. Don\'t worry it is simple and quickly done.<br/>';
            print 'We now use Grid Renderers to render the special columns.<br/>';
            print '<strong>Look for string "' . implode('", "', $systemFields) . '" in Configuration/* and replace by the following:</strong><br/>';

            print <<<EOF
<pre>
'columns' => array(

		# Config with key "__checkbox" must be replaced by:
		'__checkbox' => array(
			'renderer' => new \Fab\Vidi\Grid\CheckBoxComponent(),
		),
		...

		# Config with key "__buttons" must be replaced by:
		'__buttons' => array(
			'renderer' => new \Fab\Vidi\Grid\ButtonGroupComponent(),
		),
);
</pre>

EOF;
            print 'Don\'t forget to clear the cache afterwards.<br/>';
            exit();
        }

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
