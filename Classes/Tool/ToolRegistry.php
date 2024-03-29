<?php

namespace Fab\Vidi\Tool;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Closure;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Tool registry for a Vidi module.
 */
class ToolRegistry implements SingletonInterface
{
    /**
     * @var array
     */
    protected $tools = [];

    /**
     * @var array
     */
    protected $overriddenPermissions = [];

    /**
     * Returns a class instance.
     *
     * @return \Fab\Vidi\Tool\ToolRegistry|object
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Tool\ToolRegistry::class);
    }

    /**
     * Register a tool for a data type.
     *
     * @param string $dataType corresponds to the table name or can be "*" for all data types.
     * @param string $toolName class name which must implement "ToolInterface".
     * @return $this
     */
    public function register($dataType, $toolName)
    {
        if (!isset($this->tools[$dataType])) {
            $this->tools[$dataType] = [];
        }

        $this->tools[$dataType][] = $toolName;
        return $this;
    }

    /**
     * Override permissions for a tool by passing a Closure that will be evaluated when checking permissions.
     *
     * @param string $dataType corresponds to the table name or can be "*" for all data types.
     * @param string $toolName class name which must implement "ToolInterface".
     * @param $permission
     * @return $this
     */
    public function overridePermission($dataType, $toolName, Closure $permission)
    {
        if (empty($this->overriddenPermissions[$dataType])) {
            $this->overriddenPermissions[$dataType] = [];
        }

        $this->overriddenPermissions[$dataType][$toolName] = $permission;
        return $this;
    }

    /**
     * Un-Register a tool for a given data type.
     *
     * @param string $dataType corresponds to the table name or can be "*" for all data types.
     * @param string $toolName class name which must implement "ToolInterface".
     * @return $this
     */
    public function unRegister($dataType, $toolName)
    {
        if ($this->hasTools($dataType, $toolName)) {
            $toolPosition = array_search($toolName, $this->tools['*']);
            if ($toolPosition !== false) {
                unset($this->tools['*'][$toolPosition]);
            }

            $toolPosition = array_search($toolName, $this->tools[$dataType]);
            if ($toolPosition !== false) {
                unset($this->tools[$dataType][$toolPosition]);
            }
        }

        return $this;
    }

    /**
     * Tell whether the given data type has any tools registered.
     *
     * @param string $dataType
     * @return bool
     */
    public function hasAnyTools($dataType)
    {
        $tools = $this->getTools($dataType);
        return !empty($tools);
    }

    /**
     * Tell whether the given data type has this $tool.
     *
     * @param string $dataType
     * @param string $tool
     * @return bool
     */
    public function hasTools($dataType, $tool)
    {
        return in_array($tool, $this->tools['*']) || in_array($tool, $this->tools[$dataType]);
    }

    /**
     * Tell whether the given tool is allowed for this data type.
     *
     * @param string $dataType
     * @param string $toolName
     * @return bool
     */
    public function isAllowed($dataType, $toolName)
    {
        $isAllowed = false;

        if ($this->hasTools($dataType, $toolName)) {
            $permission = $this->getOverriddenPermission($dataType, $toolName);
            if (!is_null($permission)) {
                $isAllowed = $permission();
            } else {
                /** @var ToolInterface $toolName */
                $toolName = GeneralUtility::makeInstance($toolName);
                $isAllowed = $toolName->isShown();
            }
        }
        return $isAllowed;
    }

    /**
     * Get Registered tools.
     *
     * @param string $dataType
     * @return ToolInterface[]
     */
    public function getTools($dataType)
    {
        $tools = [];

        foreach (array($dataType, '*') as $toolSource) {
            if (isset($this->tools[$toolSource])) {
                $toolNames = $this->tools[$toolSource];

                foreach ($toolNames as $toolName) {
                    /** @var ToolInterface $tool */
                    if ($this->isAllowed($dataType, $toolName)) {
                        $tools[] = GeneralUtility::makeInstance($toolName);
                    }
                }
            }
        }
        return $tools;
    }

    /**
     * Get the proper permission for a tool.
     *
     * @param string $dataType corresponds to the table name or can be "*" for all data types.
     * @param string $toolName class name which must implement "ToolInterface".
     * @return null|Closure
     */
    protected function getOverriddenPermission($dataType, $toolName)
    {
        $permission = null;
        if (isset($this->overriddenPermissions[$dataType][$toolName])) {
            $permission = $this->overriddenPermissions[$dataType][$toolName];
        } elseif (isset($this->overriddenPermissions['*'][$toolName])) {
            $permission = $this->overriddenPermissions['*'][$toolName];
        }
        return $permission;
    }
}
