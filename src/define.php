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

define('PUBLISHPRESS_VERSION', '1.0.0');

define('PUBLISHPRESS_ROOT_PATH', dirname(__FILE__));
define('PUBLISHPRESS_FILE_PATH', PUBLISHPRESS_ROOT_PATH . '/' . basename(__FILE__));
define('PUBLISHPRESS_LIBRARY_PATH', PUBLISHPRESS_ROOT_PATH . '/library');

define('PUBLISHPRESS_URL', plugins_url('/', __FILE__));

define('PUBLISHPRESS_SETTINGS_PAGE', add_query_arg('page', 'pp-settings', get_admin_url(null, 'admin.php')));
