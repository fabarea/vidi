<?php
namespace Fab\Vidi\Tests\Functional;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Class AbstractFunctionalTestCase
 */
abstract class AbstractFunctionalTestCase extends FunctionalTestCase {

	/** @var ObjectManagerInterface The object manager */
	protected $objectManager;

	protected $testExtensionsToLoad = array('typo3conf/ext/vidi');

	protected $coreExtensionsToLoad = array('extbase', 'fluid', 'scheduler');

	public function setUp() {
		parent::setUp();
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	}

}
