<?php
/**
 * @package PublishPress
 * @author PressShack
 *
 * Copyright (c) 2017 PressShack
 *
 * ------------------------------------------------------------------------------
 * Based on Edit Flow
 * Author: Daniel Bachhuber, Scott Bressler, Mohammad Jangda, Automattic, and
 * others
 * Copyright (c) 2009-2016 Mohammad Jangda, Daniel Bachhuber, et al.
 * ------------------------------------------------------------------------------
 *
 * This file is part of PublishPress
 *
 * PublishPress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PublishPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Publishpress\Framework;

use \Pimple\Container;
use \Publishpress\Core;

/**
 * Provides a OOP interface for the WordPress' methods. This allows to create
 * mocks for Unit Tests, avoiding call external dependencies.
 */
class FunctionsProvider extends Core\ServiceAbstract
{
    /**
     * Call a function find on the global context. Used to call native WordPress
     * functions allowing to override their call while making Unit Tests,
     * returning the respective value.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // Check if the method exists on this class
        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $arguments);
        }

        // The method doesn't exists here, let's try calling it from the
        // global context

        // Break the method name
        $pieces = preg_split('/(?=[A-Z])/', $name);

        // WP method name
        $wpName = '\\' . strtolower(implode('_', $pieces));

        if (!function_exists($wpName)) {
            throw new \Exception("Undefined method {$wpName}", 1);
        }

        return call_user_func_array($wpName, $arguments);
    }
}
