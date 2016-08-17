<?php
namespace Fab\Vidi\Domain\Validator;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use Fab\Vidi\Tool\ToolRegistry;

/**
 * Validate the Tool class name before being instantiated.
 */
class ToolValidator extends AbstractValidator
{

    /**
     * Check whether $tool is valid.
     *
     * @param string $tool
     * @return void
     */
    public function isValid($tool)
    {

        $dataType = $this->getModuleLoader()->getDataType();
        $isValid = ToolRegistry::getInstance()->isAllowed($dataType, $tool);

        if (!$isValid) {
            $message = sprintf('This Tool "%s" is not allowed for the current data type.', $tool);
            $this->addError($message, 1409041510);
        }

        if (!class_exists($tool)) {
            $message = sprintf('I could not find class "%s"', $tool);
            $this->addError($message, 1409041511);
        }
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Module\ModuleLoader
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
    }
}
