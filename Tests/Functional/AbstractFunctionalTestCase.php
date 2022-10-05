<?php

namespace Fab\Vidi\Tests\Functional;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class AbstractFunctionalTestCase
 */
abstract class AbstractFunctionalTestCase extends FunctionalTestCase
{
    protected $testExtensionsToLoad = array('typo3conf/ext/vidi');

    protected $coreExtensionsToLoad = array('extbase', 'fluid', 'scheduler');

    public function setUp(): void
    {
        parent::setUp();
    }
}
