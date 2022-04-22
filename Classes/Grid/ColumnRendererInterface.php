<?php
namespace Fab\Vidi\Grid;

use Fab\Vidi\Domain\Model\Content;
/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
/**
 * Interface for rendering a column in the Grid.
 */
interface ColumnRendererInterface
{

    /**
     * Render a column in the Grid.
     *
     * @return string
     */
    public function render();

    /**
     * @param Content $object
     * @return $this
     */
    public function setObject($object);

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName($fieldName);

    /**
     * @param int $index
     * @return $this
     */
    public function setRowIndex($index);

    /**
     * @param array $configuration
     * @return $this
     */
    public function setFieldConfiguration($configuration);

    /**
     * @param array $configuration
     * @return $this
     */
    public function setGridRendererConfiguration($configuration);

    /**
     * @return array
     */
    public function getConfiguration();

}
