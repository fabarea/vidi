<?php
namespace Fab\Vidi\Grid;

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

use Fab\Vidi\Tests\Functional\AbstractFunctionalTestCase;

require_once dirname(dirname(__FILE__)) . '/AbstractFunctionalTestCase.php';

/**
 * Test case for class \Fab\Vidi\Grid\CategoryRenderer.
 */
class RelationRendererTest extends AbstractFunctionalTestCase {

	/**
	 * @var \Fab\Vidi\Grid\RelationRenderer
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
		$moduleLoader = new \Fab\Vidi\Module\ModuleLoader($this->dataType);
		$moduleLoader->register();
		$GLOBALS['_GET']['M'] = $this->moduleCode;
		$this->fixture = new \Fab\Vidi\Grid\RelationRenderer();
	}

	public function tearDown() {
		unset($this->fixture, $GLOBALS['_GET']['M']);
	}

	/**
	 * @test
	 */
	public function renderAssetWithNoCategoryReturnsEmpty() {
		$content = new \Fab\Vidi\Domain\Model\Content($this->dataType);
		$this->markTestIncomplete(); # TCA must be faked
		#$actual = $this->fixture->setObject($content)->render();
	}
}