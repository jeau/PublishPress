<?php
/**
 * Plugin Name: PublishPress
 * Plugin URI: https://pressshack.com/publishpress/
 * Description: The essential plugin for any WordPress site with multiple writers
 * Author: PressShack
 * Author URI: https://pressshack.com
 * Version: 1.0.4b1
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
 * GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package PublishPress
 * @category Core
 * @author PressShack
 */

require_once 'include.php';

echo PublishPress\Factory::getContainer()->caller->ahaTest('Opa!');
die;

// Publishpress\Factory::getContainer()->plugin->setup();
