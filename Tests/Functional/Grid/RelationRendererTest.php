<?php
namespace TYPO3\CMS\Vidi\Grid;

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
 * Test case for class \TYPO3\CMS\Vidi\Grid\CategoryRenderer.
 */
class RelationRendererTest extends \TYPO3\CMS\Vidi\Tests\Functional\AbstractFunctionalTestCase {

	/**
	 * @var \TYPO3\CMS\Vidi\Grid\RelationRenderer
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
		$this->fixture = new \TYPO3\CMS\Vidi\Grid\RelationRenderer();
	}

	public function tearDown() {
		unset($this->fixture, $GLOBALS['_GET']['M']);
	}

	/**
	 * @test
	 */
	public function renderAssetWithNoCategoryReturnsEmpty() {
		$content = new \TYPO3\CMS\Vidi\Domain\Model\Content($this->dataType);
		$this->markTestIncomplete(); # TCA must be faked
		#$actual = $this->fixture->setObject($content)->render();
	}
}
?>