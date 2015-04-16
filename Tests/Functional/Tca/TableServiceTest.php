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
 * Test case for class \TYPO3\CMS\Vidi\Tca\TableService.
 */
class TableServiceTest extends \TYPO3\CMS\Vidi\Tests\Functional\AbstractFunctionalTestCase {

	/**
	 * @var \TYPO3\CMS\Vidi\Tca\TableService
	 */
	private $fixture;

	public function setUp() {
		parent::setUp();
		$tableName = 'fe_users';
		$serviceType = 'table';
		$this->fixture = new \TYPO3\CMS\Vidi\Tca\TableService($tableName, $serviceType);
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getLabelReturnNameAsValue() {
		$this->assertEquals('username', $this->fixture->getLabelField());
	}

	/**
	 * @test
	 */
	public function getSearchableFieldsIsNotEmptyByDefaultForTableSysFile() {
		$actual = $this->fixture->getSearchFields();
		$this->assertNotEmpty($actual);
	}

}
?>