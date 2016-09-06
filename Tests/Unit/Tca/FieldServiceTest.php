<?php
namespace Fab\Vidi\Tests\Unit\Tca;

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

use Fab\Vidi\Tca\FieldType;

/**
 * Test case for class \Fab\Vidi\Tca\FieldService.
 */
class FieldServiceTest extends AbstractServiceTest {

	/**
	 * @var \Fab\Vidi\Tca\TableService
	 */
	private $fixture;

	public function setUp() {
		parent::setUp();
		$this->fixture = new \Fab\Vidi\Tca\TableService('tx_foo');
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
		$field = $this->fixture->field('username');
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
	public function fieldFirstNameMustNotBeRequiredByDefault() {
		$field = $this->fixture->field('first_name');
		$this->assertFalse($field->isRequired());
	}

	/**
	 * @test
	 */
	public function getTypeForFieldStarTimeReturnsDataTime() {
		$fieldType = $this->fixture->field('starttime')->getType();
		$this->assertEquals(FieldType::DATETIME, $fieldType);
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
			array('username', false, false, false, false, false),
			#array('usergroup', true, false, true),
		);
	}

	// @todo implement Unit Tests for relation. Below example of TCA...

}
