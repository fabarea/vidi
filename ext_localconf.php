<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get('vidi');

    if (false === isset($configuration['autoload_typoscript']) || true === (bool)$configuration['autoload_typoscript']) {

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            'vidi',
            'constants',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:vidi/Configuration/TypoScript/constants.txt">'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            'vidi',
            'setup',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:vidi/Configuration/TypoScript/setup.txt">'
        );
    }

    // Initialize generic Vidi modules after the TCA is loaded.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'Fab\Vidi\Configuration\VidiModulesAspect';

    // Initialize generic grid TCA for all data types
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'Fab\Vidi\Configuration\TcaGridAspect';

    // cache configuration, see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/CachingFramework/Configuration/Index.html#cache-configurations
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['vidi']['frontend'] = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['vidi']['groups'] = array('all', 'vidi');
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['vidi']['options']['defaultLifetime'] = 2592000;

    // Initialize the object 'TYPO3_DB'
    if (!$GLOBALS['TYPO3_DB']) {

        // Initialize database connection in $GLOBALS and connect
        $databaseConnection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Fab\Vidi\Database\DatabaseConnection::class);
        $databaseConnection->setDatabaseName(
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] ?? ''
        );
        $databaseConnection->setDatabaseUsername(
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'] ?? ''
        );
        $databaseConnection->setDatabasePassword(
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'] ?? ''
        );

        $databaseHost = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'] ?? '';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port'])) {
            $databaseConnection->setDatabasePort($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port']);
        } elseif (strpos($databaseHost, ':') > 0) {
            // @TODO: Find a way to handle this case in the install tool and drop this
            list($databaseHost, $databasePort) = explode(':', $databaseHost);
            $databaseConnection->setDatabasePort($databasePort);
        }
        if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['unix_socket'])) {
            $databaseConnection->setDatabaseSocket(
                $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['unix_socket']
            );
        }
        $databaseConnection->setDatabaseHost($databaseHost);

        $databaseConnection->debugOutput = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sqlDebug'] ?? false;

        if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['persistentConnection'])
            && $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['persistentConnection']
        ) {
            $databaseConnection->setPersistentDatabaseConnection(true);
        }

        $isDatabaseHostLocalHost = in_array($databaseHost, ['localhost', '127.0.0.1', '::1'], true);
        if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driverOptions'])
            && $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driverOptions'] & MYSQLI_CLIENT_COMPRESS
            && !$isDatabaseHostLocalHost
        ) {
            $databaseConnection->setConnectionCompression(true);
        }

        if (!empty($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['initCommands'])) {
            $commandsAfterConnect = TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
                LF,
                str_replace(
                    '\' . LF . \'',
                    LF,
                    $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['initCommands']
                ),
                true
            );
            $databaseConnection->setInitializeCommandsAfterConnect($commandsAfterConnect);
        }

        $GLOBALS['TYPO3_DB'] = $databaseConnection;
        $GLOBALS['TYPO3_DB']->initialize();
    }
});