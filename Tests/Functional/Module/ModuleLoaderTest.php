<?php

namespace Fab\Vidi\Tests\Module\Functional;

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
use Fab\Vidi\Module\ModuleLoader;
use Fab\Vidi\Tests\Functional\AbstractFunctionalTestCase;

require_once dirname(dirname(__FILE__)) . '/AbstractFunctionalTestCase.php';

/**
 * Test case for class \Fab\Vidi\Module\ModuleLoader.
 */
class ModuleLoaderTest extends AbstractFunctionalTestCase
{
    /**
     * @var ModuleLoader
     */
    private $fixture;

    /**
     * @var string
     */
    private $dataType = 'tx_foo';

    /**
     * @var string
     */
    private $moduleCode = 'user_VidiTxFooM1';


    public function setUp()
    {
        parent::setUp();
        $this->fixture = new ModuleLoader($this->dataType);
        $this->fixture->register();
    }

    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     * @dataProvider attributeValueProvider
     */
    public function attributeCanBeSet($attribute, $value)
    {
        $setter = 'set' . ucfirst($attribute);
        $this->fixture->$setter($value);
        $this->assertAttributeEquals($value, $attribute, $this->fixture);
    }

    /**
     * Provider
     */
    public function attributeValueProvider()
    {
        return array(
            array('icon', 'bar'),
            array('moduleLanguageFile', 'bar'),
        );
    }

    /**
     * @test
     * @dataProvider attributeProvider
     */
    public function testAttribute($attribute, $defaultValue)
    {
        $this->assertAttributeEquals($defaultValue, $attribute, $this->fixture);
    }

    /**
     * Provider
     */
    public function attributeProvider()
    {
        return array(
            array('dataType', $this->dataType),
            array('moduleKey', 'm1'),
            array('icon', 'EXT:vidi/ext_icon.gif')
        );
    }

    /**
     * @test
     */
    public function getModuleConfigurationReturnsArrayWithSomeKeys()
    {
        $moduleLoader = new ModuleLoader($this->dataType);
        $moduleLoader->register();
        $GLOBALS['_GET']['M'] = $this->moduleCode;

        $moduleConfiguration = $moduleLoader->getModuleConfiguration();
        $keys = array('dataType', 'additionalJavaScriptFiles', 'additionalStyleSheetFiles');
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $moduleConfiguration);
        }
    }

    /**
     * @test
     */
    public function getModuleConfigurationWithParameterDataTypeReturnsDataType()
    {
        $moduleLoader = new ModuleLoader($this->dataType);
        $moduleLoader->register();
        $GLOBALS['_GET']['M'] = $this->moduleCode;
        $this->assertEquals($this->dataType, $moduleLoader->getModuleConfiguration('dataType'));
    }
}
