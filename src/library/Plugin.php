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

namespace PublishPress;

use stdClass;

class Plugin
{
    /**
     * Options group
     */
    const OPTIONS_GROUP = 'publishpress_';

    /**
     * Options group name
     */
    const OPTIONS_GROUP_NAME = 'publishpress_options';

    /**
     * List of loaded modules
     */
    public $modules = array();

    /**
     * The constructor
     */
    public function __construct()
    {

    }

    /**
     * Return the current instance
     *
     * @return Plugin
     */
    public function getInstance()
    {
        $container = Factory::getContainer();

        return $container->plugin;
    }

    /**
     * Initialize the plugin adding the respective action
     */
    public function init()
    {
        add_action('plugins_loaded', array($this, 'getInstance'));
    }
}
