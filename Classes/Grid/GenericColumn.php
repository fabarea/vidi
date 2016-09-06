<?php
namespace Fab\Vidi\Grid;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class for configuring a column in the Grid.
 * @deprecated will be removed in Vidi 2.0 + 2. Use ColumnRendererAbstract instead.
 */
class GenericColumn implements ColumnInterface
{

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * Constructor of a Generic component in Vidi.
     *
     * @param string $className
     * @param array $configuration
     */
    public function __construct($className, $configuration = array())
    {
        $this->className = $className;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     * @deprecated will be removed in Vidi 2.0 + 2.
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return array
     * @deprecated will be removed in Vidi 2.0 + 2.
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return array
     * @deprecated will be removed in Vidi 2.0 + 2.
     */
    public function toArray()
    {
        return array(
            'partial' => $this->getClassName(),
            'configuration' => $this->getConfiguration(),
        );
    }

    /**
     * Magic method implementation for retrieving state.
     *
     * @param array $states
     * @return GenericColumn
     */
//    static public function __set_state($states)
//    {
//        return new GenericColumn($states['className'], $states['configuration']);
//    }
}
