<?php

namespace App\ChainCommandBundle\Helper;

/**
 * Trait ChainTrait
 * @package App\ChainCommandBundle\Service
 */
trait ChainTrait
{
    /**
     * check is parent
     * @param string $commandName Current command name
     * @return bool
     */
    public function isParent(string $commandName): bool
    {
        foreach ($this->chainCommands as $chain) {
            if ($commandName == $chain['parent']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is child
     * @param string $commandName Current command name
     * @return bool
     */
    public function isChild(string $commandName): bool
    {
        $isChild = false;
        foreach ($this->chainCommands as $chain) {
            if (in_array($commandName, $chain['children'])) {
                $isChild = true;
            } elseif ($commandName == $chain['parent']) {
                return false;
            }
        }

        return $isChild;
    }

    /**
     * Get parent commands for current command
     * @param string $commandName Current command name
     * @return array
     */
    public function getParents(string $commandName): array
    {
        $parents = [];
        foreach ($this->chainCommands as $chain) {
            if ($commandName === $chain['children']) {
                $parents[] = $chain['parent'];
            }
        }

        return $parents;
    }

    /**
     * Get children commands for current command
     * @param string $commandName Current command name
     * @return array
     */
    public function getChildren(string $commandName): array
    {
        $children = [];
        foreach ($this->chainCommands as $chain) {
            if ($commandName === $chain['parent']) {
                $children = $chain['children'];
            }
        }

        return $children;
    }
}
