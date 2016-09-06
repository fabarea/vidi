<?php
namespace Fab\Vidi\Configuration;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class for handling configuration of the extension
 */
class ConfigurationUtility implements SingletonInterface
{

    /**
     * @var string
     */
    protected $extensionKey = 'vidi';

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * Returns a class instance.
     *
     * @return \Fab\Vidi\Configuration\ConfigurationUtility
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Configuration\ConfigurationUtility');
    }

    /**
     * Constructor
     *
     * @return \Fab\Vidi\Configuration\ConfigurationUtility
     */
    public function __construct()
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
        $configuration = $configurationUtility->getCurrentConfiguration($this->extensionKey);

        // Fill up configuration array with relevant values.
        foreach ($configuration as $key => $data) {
            $this->configuration[$key] = $data['value'];
        }
    }

    /**
     * Returns a setting key.
     *
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        return isset($this->configuration[$key]) ? $this->configuration[$key] : null;
    }

    /**
     * Set a setting key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->configuration[$key] = $value;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
