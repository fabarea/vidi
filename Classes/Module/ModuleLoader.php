<?php
namespace Fab\Vidi\Module;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Fab\Vidi\Exception\InvalidKeyInArrayException;
use Fab\Vidi\Service\BackendUserPreferenceService;

/**
 * Service class used in other extensions to register a vidi based backend module.
 */
class ModuleLoader
{

    /**
     * Define the default main module
     */
    const DEFAULT_MAIN_MODULE = 'content';

    /**
     * Define the default pid
     */
    const DEFAULT_PID = 0;

    /**
     * The type of data being listed (which corresponds to a table name in TCA)
     *
     * @var string
     */
    protected $dataType;

    /**
     * @var string
     */
    protected $defaultPid;

    /**
     * @var bool
     */
    protected $isPidIgnored = false;

    /**
     * @var bool
     */
    protected $showPageTree = false;

    /**
     * @var bool
     */
    protected $isShown = true;

    /**
     * @var string
     */
    protected $access;

    /**
     * @var string
     */
    protected $mainModule;

    /**
     * @var string
     */
    protected $position = '';

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $moduleLanguageFile;

    /**
     * The module key such as m1, m2.
     *
     * @var string
     */
    protected $moduleKey = 'm1';

    /**
     * @var string[]
     */
    protected $additionalJavaScriptFiles = [];

    /**
     * @var string[]
     */
    protected $additionalStyleSheetFiles = [];

    /**
     * @var array
     */
    protected $components = [];

    /**
     * @param string $dataType
     */
    public function __construct($dataType = null)
    {
        $this->dataType = $dataType;

        // Initialize components
        $this->components = [
            ModulePosition::DOC_HEADER => [
                ModulePosition::TOP => [
                    ModulePosition::LEFT => [],
                    ModulePosition::RIGHT => [
                        \Fab\Vidi\View\Button\ToolButton::class,
                    ],
                ],
                ModulePosition::BOTTOM => [
                    ModulePosition::LEFT => [
                        \Fab\Vidi\View\Button\NewButton::class,
                        \Fab\Vidi\ViewHelpers\Link\BackViewHelper::class,
                    ],
                    ModulePosition::RIGHT => [],
                ],
            ],
            ModulePosition::GRID => [
                ModulePosition::TOP => [
                    \Fab\Vidi\View\Check\PidCheck::class,
                    \Fab\Vidi\View\Check\RelationsCheck::class,
                    #\Fab\Vidi\View\Tab\DataTypeTab::class,
                ],
                ModulePosition::BUTTONS => [
                    \Fab\Vidi\View\Button\EditButton::class,
                    \Fab\Vidi\View\Button\DeleteButton::class,
                ],
                ModulePosition::BOTTOM => [],
            ],
            ModulePosition::MENU_MASS_ACTION => [
                \Fab\Vidi\View\MenuItem\ExportXlsMenuItem::class,
                \Fab\Vidi\View\MenuItem\ExportXmlMenuItem::class,
                \Fab\Vidi\View\MenuItem\ExportCsvMenuItem::class,
                \Fab\Vidi\View\MenuItem\DividerMenuItem::class,
                \Fab\Vidi\View\MenuItem\MassDeleteMenuItem::class,
                #\Fab\Vidi\View\MenuItem\MassEditMenuItem::class,
            ],
        ];
    }

    /**
     * Tell whether a module is already registered.
     *
     * @param string $dataType
     * @return bool
     */
    public function isRegistered($dataType): bool
    {
        $internalModuleSignature = $this->getInternalModuleSignature($dataType);
        return !empty($GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleSignature]);
    }

    /**
     * @return array
     */
    protected function getExistingInternalConfiguration(): array
    {
        $internalModuleSignature = $this->getInternalModuleSignature();
        return is_array($GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleSignature])
            ? $GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleSignature]
            : [];
    }

    /**
     * @return array
     */
    protected function getExistingMainConfiguration(): array
    {
        $possibleConfiguration = $GLOBALS['TBE_MODULES']['_configuration'][$this->computeMainModule() . '_' . $this->getInternalModuleSignature()];
        return is_array($possibleConfiguration) ? $possibleConfiguration : [];
    }

    /**
     * @return string
     */
    protected function computeMainModule(): string
    {
        $existingConfiguration = $this->getExistingInternalConfiguration();

        if ($this->mainModule !== null) {
            $mainModule = $this->mainModule;
        } elseif ($existingConfiguration['mainModule']) { // existing configuration may override.
            $mainModule = $existingConfiguration['mainModule'];
        } else {
            $mainModule = self::DEFAULT_MAIN_MODULE; //default value.
        }
        return $mainModule;
    }

    /**
     * @return string
     */
    protected function computeDefaultPid(): string
    {
        $existingConfiguration = $this->getExistingInternalConfiguration();

        if ($this->defaultPid !== null) {
            $defaultPid = $this->defaultPid;
        } elseif ($existingConfiguration['defaultPid']) { // existing configuration may override.
            $defaultPid = $existingConfiguration['defaultPid'];
        } else {
            $defaultPid = self::DEFAULT_PID; //default value.
        }
        return $defaultPid;
    }

    /**
     * @return array
     */
    protected function computeAdditionalJavaScriptFiles(): array
    {
        $additionalJavaScriptFiles = $this->additionalJavaScriptFiles;

        // Possible merge of existing javascript files.
        $existingConfiguration = $this->getExistingInternalConfiguration();
        if ($existingConfiguration['additionalJavaScriptFiles']) {
            $additionalJavaScriptFiles = array_merge($additionalJavaScriptFiles, $existingConfiguration['additionalJavaScriptFiles']);
        }

        return $additionalJavaScriptFiles;
    }

    /**
     * @return array
     */
    protected function computeAdditionalStyleSheetFiles(): array
    {
        $additionalStyleSheetFiles = $this->additionalStyleSheetFiles;

        // Possible merge of existing style sheets.
        $existingConfiguration = $this->getExistingInternalConfiguration();
        if ($existingConfiguration['additionalStyleSheetFiles']) {
            $additionalStyleSheetFiles = array_merge($additionalStyleSheetFiles, $existingConfiguration['additionalStyleSheetFiles']);
        }

        return $additionalStyleSheetFiles;
    }

    /**
     * @return array
     */
    protected function computeComponents(): array
    {
        // We override the config in any case. See if we need more than that.
        return $this->components;
    }

    /**
     * Register the module in two places: core + vidi internal.
     *
     * @return void
     */
    public function register(): void
    {
        // Internal Vidi module registration.
        $configuration = [];
        $configuration['dataType'] = $this->dataType;
        $configuration['mainModule'] = $this->computeMainModule();
        $configuration['defaultPid'] = $this->computeDefaultPid();
        $configuration['additionalJavaScriptFiles'] = $this->computeAdditionalJavaScriptFiles();
        $configuration['additionalStyleSheetFiles'] = $this->computeAdditionalStyleSheetFiles();
        $configuration['components'] = $this->computeComponents();
        $configuration['isPidIgnored'] = $this->isPidIgnored;

        $internalModuleSignature = $this->getInternalModuleSignature();
        $GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleSignature] = $configuration;

        // Core module registration.
        // Register and displays module in the BE only if told, default is "true".
        if ($this->isShown) {

            $moduleConfiguration = [];
            #$moduleConfiguration['routeTarget'] = \Fab\Vidi\Controller\ContentController::class . '::mainAction', // what to do here?
            $moduleConfiguration['access'] = $this->getAccess();
            $moduleConfiguration['labels'] = $this->getModuleLanguageFile();
            $icon = $this->getIcon();
            if ($icon) {
                $moduleConfiguration['icon'] = $icon;
            }

            if ($this->showPageTree) {
                $moduleConfiguration['navigationComponentId'] = 'TYPO3/CMS/Backend/PageTree/PageTreeElement';
                $moduleConfiguration['inheritNavigationComponentFromMainModule'] = true;
            } else {
                $moduleConfiguration['inheritNavigationComponentFromMainModule'] = true;
            }

            ExtensionUtility::registerModule(
                'Fab.vidi',
                $this->computeMainModule(),
                $this->dataType . '_' . $this->moduleKey,
                $this->position,
                [
                    'Content' => 'index, list, delete, update, edit, copy, move, localize, sort, copyClipboard, moveClipboard',
                    'Tool' => 'welcome, work',
                    'Facet' => 'autoSuggest, autoSuggests',
                    'Selection' => 'edit, update, create, delete, list, show',
                    'UserPreferences' => 'save',
                    'Clipboard' => 'save, flush, show',
                ],
                $moduleConfiguration
            );
        }
    }

    /**
     * Return the module code for a BE module.
     *
     * @return string
     */
    public function getSignature(): string
    {
        $signature = GeneralUtility::_GP(Parameter::MODULE);
        $trimmedSignature = trim($signature, '/');
        return str_replace('/', '_', $trimmedSignature);
    }

    /**
     * Returns the current pid.
     *
     * @return int
     */
    public function getCurrentPid(): int
    {
        return GeneralUtility::_GET(Parameter::PID) > 0 ? (int)GeneralUtility::_GET(Parameter::PID) : 0;
    }

    /**
     * Return the module URL.
     *
     * @param array $additionalParameters
     * @return string
     */
    public function getModuleUrl(array $additionalParameters = []): string
    {
        $moduleCode = $this->getSignature();

        // And don't forget the pid!
        if (GeneralUtility::_GET(Parameter::PID)) {
            $additionalParameters[Parameter::PID] = GeneralUtility::_GET(Parameter::PID);
        }

        return BackendUtility::getModuleUrl($moduleCode, $additionalParameters);
    }

    /**
     * Return the parameter prefix for a BE module.
     *
     * @return string
     */
    public function getParameterPrefix(): string
    {
        return 'tx_vidi_' . strtolower($this->getSignature());
    }

    /**
     * Return a configuration key or the entire module configuration array if not key is given.
     *
     * @param string $key
     * @return mixed
     */
    public function getModuleConfiguration($key = '')
    {

        $vidiModuleCode = $this->getSignature();

        // Module code must exist
        if (empty($GLOBALS['TBE_MODULES_EXT']['vidi'][$vidiModuleCode])) {
            $message = sprintf('Invalid or not existing module code "%s"', $vidiModuleCode);
            throw new InvalidKeyInArrayException($message, 1375092053);
        }

        $result = $GLOBALS['TBE_MODULES_EXT']['vidi'][$vidiModuleCode];

        if (!empty($key)) {
            if (isset($result[$key])) {
                $result = $result[$key];
            } else {
                // key must exist
                $message = sprintf('Invalid key configuration "%s"', $key);
                throw new InvalidKeyInArrayException($message, 1375092054);
            }
        }
        return $result;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string
     */
    protected function getIcon(): string
    {
        $moduleConfiguration = $this->getExistingMainConfiguration();


        if ($this->icon) {
            $icon = $this->icon;
        } elseif ($moduleConfiguration['icon']) { // existing configuration may override.
            $icon = $moduleConfiguration['icon'];
        } else {
            $icon = ''; //default value.
        }

        return $icon;
    }

    /**
     * @param string $mainModule
     * @return $this
     */
    public function setMainModule($mainModule): self
    {
        $this->mainModule = $mainModule;
        return $this;
    }

    /**
     * @return string
     */
    public function getMainModule(): string
    {
        if ($this->mainModule === null) {
            $this->mainModule = $this->getModuleConfiguration('mainModule');
        }
        return $this->mainModule;
    }

    /**
     * @param string $moduleLanguageFile
     * @return $this
     */
    public function setModuleLanguageFile($moduleLanguageFile): self
    {
        $this->moduleLanguageFile = $moduleLanguageFile;
        return $this;
    }

    /**
     * @param string $component
     * @return $this
     */
    public function removeComponentFromDocHeader(string $component): self
    {
        foreach ($this->components[ModulePosition::DOC_HEADER] as $verticalPosition => $docHeaders) {
            foreach ($docHeaders as $horizontalPosition => $docHeader) {

                $index = array_search($component, $docHeader, true);
                if ($index !== false) {
                    // $verticalPosition: top or bottom
                    // $horizontalPosition: left or right
                    unset($this->components[ModulePosition::DOC_HEADER][$verticalPosition][$horizontalPosition][$index]);
                }
            }
        }
        return $this;
    }

    /**
     * @param bool $isPidIgnored
     * @return $this
     */
    public function ignorePid(bool $isPidIgnored): self
    {
        $this->isPidIgnored = $isPidIgnored;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPidIgnored(): bool
    {
        return $this->getModuleConfiguration('isPidIgnored');
    }

    /**
     * @param string $component
     * @return bool
     */
    public function hasComponentInDocHeader(string $component): bool
    {
        foreach ($this->getModuleConfiguration('components')[ModulePosition::DOC_HEADER] as $verticalPosition => $docHeaders) {
            foreach ($docHeaders as $horizontalPosition => $docHeader) {

                $index = array_search($component, $docHeader, true);
                if ($index !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return string
     */
    protected function getModuleLanguageFile(): string
    {
        $moduleConfiguration = $this->getExistingMainConfiguration();

        if ($this->moduleLanguageFile) {
            $moduleLanguageFile = $this->moduleLanguageFile;
        } elseif ($moduleConfiguration['labels']) { // existing configuration may override.
            $moduleLanguageFile = $moduleConfiguration['labels'];
        }
        else {
            $moduleLanguageFile = ''; //default value.
        }

        return $moduleLanguageFile;
    }

    /**
     * @param string $position
     * @return $this
     */
    public function setPosition($position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function addJavaScriptFiles(array $files): self
    {
        foreach ($files as $file) {
            $this->additionalJavaScriptFiles[] = $file;
        }
        return $this;
    }

    /**
     * @param string $fileNameAndPath
     * @return $this
     */
    public function addJavaScriptFile($fileNameAndPath): self
    {
        $this->additionalJavaScriptFiles[] = $fileNameAndPath;
        return $this;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function addStyleSheetFiles(array $files): self
    {
        foreach ($files as $file) {
            $this->additionalStyleSheetFiles[] = $file;
        }
        return $this;
    }

    /**
     * @param string $fileNameAndPath
     * @return $this
     */
    public function addStyleSheetFile($fileNameAndPath): self
    {
        $this->additionalStyleSheetFiles[] = $fileNameAndPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        if ($this->dataType === null) {
            $this->dataType = $this->getModuleConfiguration('dataType');
        }
        return $this->dataType;
    }

    /**
     * @return array
     */
    public function getDataTypes(): array
    {
        $dataTypes = [];
        foreach ($GLOBALS['TBE_MODULES_EXT']['vidi'] as $module) {
            $dataTypes[] = $module['dataType'];
        }
        return $dataTypes;
    }

    /**
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType): self
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultPid(): string
    {
        if (empty($this->defaultPid)) {
            $this->defaultPid = $this->getModuleConfiguration('defaultPid');
        }
        return $this->defaultPid;
    }

    /**
     * @param string $defaultPid
     * @return $this
     */
    public function setDefaultPid($defaultPid): self
    {
        $this->defaultPid = $defaultPid;
        return $this;
    }

    /**
     * @param bool $isPageTreeShown
     * @return $this
     */
    public function showPageTree($isPageTreeShown): self
    {
        $this->showPageTree = $isPageTreeShown;
        return $this;
    }

    /**
     * @param string $isShown
     * @return $this
     */
    public function isShown($isShown): self
    {
        $this->isShown = $isShown;
        return $this;
    }

    /**
     * @return $array
     */
    public function getDocHeaderTopLeftComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setDocHeaderTopLeftComponents(array $components): self
    {
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addDocHeaderTopLeftComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT];
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT] = array_merge($currentComponents, $components);
        return $this;
    }

    /**
     * @return $array
     */
    public function getDocHeaderTopRightComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setDocHeaderTopRightComponents(array $components): self
    {
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addDocHeaderTopRightComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT];
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT] = array_merge($currentComponents, $components);
        return $this;
    }

    /**
     * @return $array
     */
    public function getDocHeaderBottomLeftComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setDocHeaderBottomLeftComponents(array $components): self
    {
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addDocHeaderBottomLeftComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT];
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT] = array_merge($currentComponents, $components);
        return $this;
    }

    /**
     * @return $array
     */
    public function getDocHeaderBottomRightComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setDocHeaderBottomRightComponents(array $components): self
    {
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addDocHeaderBottomRightComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT];
        $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT] = array_merge($currentComponents, $components);
        return $this;
    }

    /**
     * @return $array
     */
    public function getGridTopComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::GRID][ModulePosition::TOP];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setGridTopComponents(array $components): self
    {
        $this->components[ModulePosition::GRID][ModulePosition::TOP] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addGridTopComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::GRID][ModulePosition::TOP];
        $this->components[ModulePosition::GRID][ModulePosition::TOP] = array_merge($currentComponents, $components);
        return $this;
    }

    /**
     * @return mixed $array
     */
    public function getGridBottomComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::GRID][ModulePosition::BOTTOM];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setGridBottomComponents(array $components): self
    {
        $this->components[ModulePosition::GRID][ModulePosition::BOTTOM] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addGridBottomComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::GRID][ModulePosition::BOTTOM];
        $this->components[ModulePosition::GRID][ModulePosition::BOTTOM] = array_merge($currentComponents, $components);
        return $this;
    }

    /**
     * @return $array
     */
    public function getGridButtonsComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::GRID][ModulePosition::BUTTONS];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setGridButtonsComponents(array $components): self
    {
        $this->components[ModulePosition::GRID][ModulePosition::BUTTONS] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addGridButtonsComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::GRID][ModulePosition::BUTTONS];
        $this->components[ModulePosition::GRID][ModulePosition::BUTTONS] = array_merge($components, $currentComponents);
        return $this;
    }

    /**
     * @return $array
     */
    public function getMenuMassActionComponents()
    {
        $configuration = $this->getModuleConfiguration();
        return $configuration['components'][ModulePosition::MENU_MASS_ACTION];
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setMenuMassActionComponents(array $components): self
    {
        $this->components[ModulePosition::MENU_MASS_ACTION] = $components;
        return $this;
    }

    /**
     * @param string|array $components
     * @return $this
     */
    public function addMenuMassActionComponents($components): self
    {
        if (is_string($components)) {
            $components = [$components];
        }
        $currentComponents = $this->components[ModulePosition::MENU_MASS_ACTION];
        $this->components[ModulePosition::MENU_MASS_ACTION] = array_merge($components, $currentComponents);
        return $this;
    }

    /**
     * @return string
     */
    protected function getAccess(): string
    {
        $moduleConfiguration = $this->getExistingMainConfiguration();

        if ($this->access !== null) {
            $access = $this->access;
        } elseif ($moduleConfiguration['access']) { // existing configuration may override.
            $access = $moduleConfiguration['access'];
        } else {
            $access = Access::USER; //default value.
        }
        return $access;
    }

    /**
     * @param string $access
     * @return $this
     */
    public function setAccess($access): self
    {
        $this->access = $access;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getAdditionalJavaScriptFiles(): array
    {
        if (empty($this->additionalJavaScriptFiles)) {
            $this->additionalJavaScriptFiles = $this->getModuleConfiguration('additionalJavaScriptFiles');
        }
        return $this->additionalJavaScriptFiles;
    }

    /**
     * @return \string[]
     */
    public function getAdditionalStyleSheetFiles(): array
    {
        if (empty($this->additionalStyleSheetFiles)) {
            $this->additionalStyleSheetFiles = $this->getModuleConfiguration('additionalStyleSheetFiles');
        }
        return $this->additionalStyleSheetFiles;
    }

    /**
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param string $pluginName
     * @return bool
     */
    public function hasPlugin($pluginName = ''): bool
    {
        $parameterPrefix = $this->getParameterPrefix();
        $parameters = GeneralUtility::_GET($parameterPrefix);

        $hasPlugin = !empty($parameters['plugins']) && is_array($parameters['plugins']);
        if ($hasPlugin && $pluginName) {
            $hasPlugin = in_array($pluginName, $parameters['plugins']);
        }
        return $hasPlugin;
    }

    /**
     * Compute the internal module code
     *
     * @param null|string $dataType
     * @return string
     */
    protected function getInternalModuleSignature($dataType = null): string
    {
        // Else we forge the module signature
        if ($dataType === null) {
            $dataType = $this->dataType;
        }
        $subModuleName = $dataType . '_' . $this->moduleKey;

        $mainModule = $this->mainModule ? : self::DEFAULT_MAIN_MODULE;
        return $mainModule . '_Vidi' . GeneralUtility::underscoredToUpperCamelCase($subModuleName);
    }

}
