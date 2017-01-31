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

namespace PublishPress\Core;

/**
 * Provides a OOP interface for the WordPress' methods. This allows to create
 * mocks for Unit Tests, avoiding call external dependencies.
 */
class FunctionsProvider
{
    /**
     * Calls plugins_url
     *
     * @param string $path
     * @param string plugin
     * @return string
     * @see https://developer.wordpress.org/reference/functions/plugins_url/
     */
    public function pluginsUrl($path = '', $plugin = '')
    {
        return \plugins_url($path, $plugin);
    }

    /**
     * Calls add_query_arg
     *
     * @param string $key
     * @param string $value
     * @param string $url
     * @return string
     * @see https://developer.wordpress.org/reference/functions/add_query_arg/
     */
    public function addQueryArg($key, $value = '', $url = '')
    {
        return \add_query_arg($key, $value, $url);
    }

    /**
     * Calls get_admin_url
     *
     * @param int $blog_id
     * @param string $path
     * @param string $scheme
     * @return string
     * @see https://developer.wordpress.org/reference/functions/get_admin_url/
     */
    public function getAdminUrl($blog_id = null, $path = '', $scheme = 'admin')
    {
        return \get_admin_url($blog_id, $path, $scheme);
    }

    /**
     * Calls add_action
     *
     * @param string $tag
     * @param callable $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool
     * @see https://developer.wordpress.org/reference/functions/add_action/
     */
    public function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return \add_action($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Calls load_plugin_textdomain
     *
     * @param string $domain
     * @param string $deprecated
     * @param string $plugin_real_path
     * @return bool
     * @see https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
     */
    public function loadPluginTextdomain($domain, $deprecated = false, $plugin_real_path = false)
    {
        return \load_plugin_textdomain($domain, $deprecated, $plugin_real_path);
    }

    /**
     * Calls plugin_basename
     *
     * @param string $file
     * @return string
     * @see https://developer.wordpress.org/reference/functions/plugin_basename/
     */
    public function pluginBasename($file)
    {
        return \plugin_basename($file);
    }
}
