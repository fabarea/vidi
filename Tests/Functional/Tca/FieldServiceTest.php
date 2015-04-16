<?php
namespace TYPO3\CMS\Vidi\Tca;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once dirname(dirname(__FILE__)) . '/AbstractFunctionalTestCase.php';

/**
 * Test case for class \TYPO3\CMS\Vidi\Tca\FieldService.
 */
class FieldServiceTest extends \TYPO3\CMS\Vidi\Tests\Functional\AbstractFunctionalTestCase {

	/**
	 * @var \TYPO3\CMS\Vidi\Tca\TableService
	 */
	private $fixture;

	/**
	 * @var string
	 */
	private $dataType = 'fe_users';

	/**
	 * @var string
	 */
	private $moduleCode = 'user_VidiFeUsersM1';

	public function setUp() {
		parent::setUp();

		$moduleLoader = new \TYPO3\CMS\Vidi\Module\ModuleLoader($this->dataType);
		$moduleLoader->register();
		$GLOBALS['_GET']['M'] = $this->moduleCode;

		// @todo implement Unit Tests for relation. Below example of TCA.
		#################
		# opposite many
		# one-to-many
		$TCA['tx_foo_domain_model_book'] = array(
			'columns' => array(
				'access_codes' => array(
					'config' => array(
						'type' => 'inline',
						'foreign_table' => 'tx_foo_domain_model_accesscode',
						'foreign_field' => 'book',
						'maxitems' => 9999,
					),
				),
			),
		);

		# opposite one
		# many-to-one
		$TCA['tx_foo_domain_model_accesscode'] = array(
			'columns' => array(
				'book' => array(
					'config' => array(
						'type' => 'select',
						'foreign_table' => 'tx_foo_domain_model_book',
						'foreign_field' => 'access_codes',
						'minitems' => 1,
						'maxitems' => 1,
					),
				),
			),
		);

		#################
		# Many to many
		$TCA['tx_foo_domain_model_book'] = array(
			'columns' => array(
				'tx_myext_locations' => array(
					'config' => array(
						'type' => 'select',
						'foreign_table' => 'tx_foo_domain_categories',
						'MM_opposite_field' => 'usage_mm',
						'MM' => 'tx_foo_domain_categories_mm',
						'MM_match_fields' => array(
							'tablenames' => 'pages'
						),
						'size' => 5,
						'maxitems' => 100
					)
				)
			),
		);

		$TCA['tx_foo_domain_categories'] = array(
			'columns' => array(
				'usage_mm' => array(
					'config' => array(
						'type' => 'group',
						'internal_type' => 'db',
						'allowed' => 'pages,tt_news',
						'prepend_tname' => 1,
						'size' => 5,
						'maxitems' => 100,
						'MM' => 'tx_foo_domain_categories_mm'
					)
				)
			),
		);

		#################
		# Legacy MM relation
		$TCA['tx_foo_domain_model_book'] = array(
			'columns' => array(
				'fe_groups' => array(
					'config' => array(
						'type' => 'inline',
						'foreign_table' => 'tx_foo_domain_model_accesscode',
						'foreign_field' => 'book',
						'maxitems' => 9999,
					),
				),
			),
		);

		$tableName = 'fe_users';
		$serviceType = 'field';
		$this->fixture =  \TYPO3\CMS\Vidi\Tca\Tca::table($tableName);
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function fieldsIncludesATitleFieldInTableSysFile() {
		$actual = $this->fixture->getFields();
		$this->assertTrue(is_array($actual));
		$this->assertContains('username', $actual);
	}

	/**
	 * @test
	 */
	public function fieldTypeReturnsInputForFieldTitleInTableSysFile() {
		$field = $this->fixture->field('title');
		$actual = $field->getType();
		$this->assertEquals('text', $actual);
	}

	/**
	 * @test
	 */
	public function fieldNameMustBeRequiredByDefault() {
		$field = $this->fixture->field('username');
		$this->assertTrue($field->isRequired());
	}

	/**
	 * @test
	 */
	public function fieldTitleMustNotBeRequiredByDefault() {
		$field = $this->fixture->field('email');
		$this->assertFalse($field->isRequired());
	}

	/**
	 * @test
	 * @dataProvider fieldProvider
	 */
	public function hasRelationReturnsFalseForFieldName($fieldName, $hasRelation, $hasRelationOneToMany, $hasRelationManyToMany) {
		$field = $this->fixture->field($fieldName);
		$this->assertEquals($hasRelation, $field->hasRelation());
		$this->assertNotEquals($hasRelation, $field->hasNoRelation());
		$this->assertEquals($hasRelationOneToMany, $field->hasRelationOneToMany());
		$this->assertEquals($hasRelationOneToMany, $field->hasRelationManyToOne());
		$this->assertEquals($hasRelationManyToMany, $field->hasRelationManyToMany());
		$this->assertEquals($hasRelationOneToMany, $field->hasRelationOneToOne());
	}

	/**
	 * Provider
	 */
	public function fieldProvider() {
		return array(
			array('username', FALSE, FALSE, FALSE, FALSE, FALSE),
			#array('usergroup', TRUE, FALSE, TRUE),
		);
	}

}
?>