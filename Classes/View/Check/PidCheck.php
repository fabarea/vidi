<?php

namespace Fab\Vidi\View\Check;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Module\ModulePidService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\View\AbstractComponentView;

/**
 * Class PidCheck
 * @deprecated
 */
class PidCheck extends AbstractComponentView
{
    /**
     * Renders warnings if storagePid is not properly configured.
     *
     * @return string
     */
    public function render(): string
    {
        $errors = $this->getModulePidService()->validateConfiguredPid();

        return empty($errors)
            ? ''
            : $this->formatMessagePidIsNotValid($errors);
    }

    /**
     * Format a message whenever the storage is offline.
     *
     * @param array $errors
     * @return string
     */
    protected function formatMessagePidIsNotValid(array $errors): string
    {
        $configuredPid = $this->getModulePidService()->getConfiguredNewRecordPid();

        $error = implode('<br />', $errors);
        $dataType = $this->getModuleLoader()->getDataType();
        $result = <<< EOF
			<div class="alert alert-warning">
				<div class="alert-title">
					Page id "{$configuredPid}" has found to be a wrong configuration for "{$dataType}"
				</div>
				<div class="alert-message">
					<p>{$error}</p>
					New records cannot be created with this page id. The configuration can be changed at different levels:
					<ul>
						<li>Settings in the Extension Manager as fallback configuration.</li>
						<li>In some ext_tables.php file, by allowing this record type on any pages.<br />
						\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('{$dataType}')
						</li>
						<li>By User TSconfig:</li>
					</ul>
<pre>
# User TSconfig to be placed in your ext_tables.php:
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('

	# Default pid for "{$dataType}" in Vidi:
	tx_vidi.dataType.{$dataType}.storagePid = xx
');
</pre>

				</div>
			</div>
EOF;

        return $result;
    }

    /**
     * @return ModulePidService|object
     */
    public function getModulePidService()
    {
        /** @var ModulePidService $modulePidService */
        return GeneralUtility::makeInstance(ModulePidService::class);
    }

}
