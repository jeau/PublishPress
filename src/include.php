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

use Publishpress\Core\Helper;
use Publishpress\Autoloader;

defined('ABSPATH') or die("No direct script access allowed.");

if (!defined('PUBLISHPRESS_LOADED')) {
    // Autoloader
    if (!class_exists('Publishpress\\Autoloader')) {
        require_once __DIR__ . '/autoloader.php';
    }

    // Load vendor libraries
    require_once __DIR__ . '/vendor/autoload.php';


    $helper = PublishPress\Factory::getContainer()->helper;
    $caller = PublishPress\Factory::getContainer()->caller;

    $helper->defineConst('PUBLISHPRESS_ROOT_PATH', __DIR__);
    $helper->defineConst('PUBLISHPRESS_FILE_PATH', PUBLISHPRESS_ROOT_PATH . '/publishpress.php');
    $helper->defineConst('PUBLISHPRESS_VERSION', $helper->getPluginVersion(PUBLISHPRESS_FILE_PATH));
    $helper->defineConst('PUBLISHPRESS_URL', $caller->pluginsUrl('/', PUBLISHPRESS_FILE_PATH));
    $helper->defineConst('PUBLISHPRESS_SETTINGS_PAGE', $caller->addQueryArg('page', 'pp-settings', get_admin_url(null, 'admin.php')));
    // Register Publishpress' libraries
    Autoloader::register('Publishpress\\', __DIR__ . '/library');

    define('PUBLISHPRESS_LOADED', 1);
}
