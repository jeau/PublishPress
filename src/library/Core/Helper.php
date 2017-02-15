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

namespace Publishpress\Core;

use \Publishpress\Core;

class Helper extends Core\ServiceAbstract
{
    /**
     * Only define a constant if it has not been defined yet.
     * This allow us to pre-define a constant, specially for tests purposes.
     *
     * @param string $name
     * @param string $value
     */
    public function defineConst($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
    * Returns the version number found on the file comments. Used to get the
    * version of the plugin before the get_plugin_data is available
    *
    * @param string $path
    *
    * @return string
    */
    public function getPluginVersion($path)
    {
        $file = file_get_contents($path);

        preg_match('/Version:\s*([0-9\.a-z]*)/i', $file, $matches);

        return $matches[1];
    }
}
