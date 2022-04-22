<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Abstract class for rendering a column in the Grid.
 */
abstract class ColumnRendererAbstract implements ColumnRendererInterface
{

    /**
     * The content object.
     *
     * @var Content
     */
    protected $object;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var int
     */
    protected $rowIndex;

    /**
     * @var array
     */
    protected $fieldConfiguration = [];

    /**
     * @var array
     */
    protected $gridRendererConfiguration = [];

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * Constructor of a Generic component in Vidi.
     *
     * @param array $configuration
     * @param array $legacyParameterConfiguration
     */
    public function __construct($configuration = [], $legacyParameterConfiguration = array())
    {
        if (is_string($configuration)) {
            $configuration = $legacyParameterConfiguration;
            GeneralUtility::deprecationLog('ColumnRendererAbstract: first parameter must now be an array. Please edit me in ' . get_class($this));
        }
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return Content
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Content $object
     * @return $this
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * @return int
     */
    public function getRowIndex()
    {
        return $this->rowIndex;
    }

    /**
     * @param int $rowIndex
     * @return $this
     */
    public function setRowIndex($rowIndex)
    {
        $this->rowIndex = $rowIndex;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldConfiguration()
    {
        return $this->fieldConfiguration;
    }

    /**
     * @param array $fieldConfiguration
     * @return $this
     */
    public function setFieldConfiguration($fieldConfiguration)
    {
        $this->fieldConfiguration = $fieldConfiguration;
        return $this;
    }

    /**
     * @return array
     */
    public function getGridRendererConfiguration()
    {
        return $this->gridRendererConfiguration;
    }

    /**
     * @param array $gridRendererConfiguration
     * @return $this
     */
    public function setGridRendererConfiguration($gridRendererConfiguration)
    {
        $this->gridRendererConfiguration = $gridRendererConfiguration;
        return $this;
    }

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs htmlentities() function.
     *
     * @param string $value string to format
     * @param bool $keepQuotes if TRUE, single and double quotes won't be replaced (sets ENT_NOQUOTES flag)
     * @param string $encoding
     * @return string
     * @see http://www.php.net/manual/function.htmlentities.php
     * @api
     */
    protected function secure($value , $keepQuotes = false, $encoding = 'UTF-8')
    {
        $flags = $keepQuotes ? ENT_NOQUOTES : ENT_COMPAT;
        return htmlspecialchars($value, $flags, $encoding);
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return object|ModuleLoader
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }

    /**
     * @return object|IconFactory
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * @return object|LanguageService
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class);
    }

}
