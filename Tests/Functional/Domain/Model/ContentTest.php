<?php
namespace TYPO3\CMS\Vidi\Domain\Model;

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

require_once dirname(dirname(dirname(__FILE__))) . '/AbstractFunctionalTestCase.php';

/**
 * Test case for class \TYPO3\CMS\Vidi\Domain\Model\Content.
 */
class ContentTest extends \TYPO3\CMS\Vidi\Tests\Functional\AbstractFunctionalTestCase {

	/**
	 * @var \TYPO3\CMS\Vidi\Domain\Model\Content
	 */
	private $fixture;

	/**
	 * @var string
	 */
	private $dataType = 'tx_foo_domain_model_bar';

	public function setUp() {
		parent::setUp();
		$GLOBALS['TCA'][$this->dataType]['columns'] = array(
			'foo' => array(),
			'foo_bar' => array(),
		);
		$this->fixture = new \TYPO3\CMS\Vidi\Domain\Model\Content($this->dataType);
	}

	public function tearDown() {
		unset($this->fixture, $GLOBALS['TCA'][$this->dataType]);
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function fieldNameIsConvertedToPropertyName($fieldName, $propertyName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$object = new \TYPO3\CMS\Vidi\Domain\Model\Content($this->dataType, $data);
		$this->assertObjectHasAttribute($propertyName, $object);
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function accessValueOfArrayObjectReturnsFooDataAsString($fieldName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$this->markTestIncomplete(); # TCA must be faked
		#$object = new \TYPO3\CMS\Vidi\Domain\Model\Content($this->dataType, $data);
		#$this->assertSame($data[$fieldName], $object[$fieldName]);
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function getValueThroughGetterReturnsFooDataAsString($fieldName, $propertyName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$this->markTestIncomplete(); # TCA must be faked
		#$object = new \TYPO3\CMS\Vidi\Domain\Model\Content($this->dataType, $data);
		#$getter = 'get' . ucfirst($propertyName);
		#$this->assertSame($data[$fieldName], $object->$getter());
	}

	/**
	 * @test
	 * @dataProvider fieldNameProvider
	 */
	public function toArrayMethodContainsGivenFieldName($fieldName) {
		$data = array(
			$fieldName => 'foo data',
		);
		$object = new \TYPO3\CMS\Vidi\Domain\Model\Content($this->dataType, $data);
		$array = $object->toArray();
		$this->assertArrayHasKey($fieldName, $array);
	}

	/**
	 * Provider
	 */
	public function fieldNameProvider() {
		return array(
			array('foo', 'foo'),
			array('foo_bar', 'fooBar'),
		);
	}

	/**
	 * @test
	 * @dataProvider propertyProvider
	 */
	public function testProperty($propertyName, $value) {
		$setter = 'set' . ucfirst($propertyName);
		$getter = 'get' . ucfirst($propertyName);
		$this->markTestIncomplete(); # TCA must be faked
		#call_user_func_array(array($this->fixture, $setter), array($value));
		#$this->assertEquals($value, call_user_func(array($this->fixture, $getter)));
	}

	/**
	 * Provider
	 */
	public function propertyProvider() {
		return array(
			array('username', 'foo'),
		);
	}
}
?>