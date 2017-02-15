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

 namespace Publishpress;

 abstract class Factory
 {
     /**
     * @var Container
     */
    protected static $container;

    /**
     * Get a OSMap container class
     *
     * @param boolean $force
     * @return Container
     * @throws \Exception
     */
    public static function getContainer($force = false)
    {
        if (empty(static::$container) || $force) {
            $config = array();

            $container = new Core\Container(
                array(
                    'configuration' => new Core\Configuration($config)
                )
            );

            $container->register(new Core\Services);

            static::$container = $container;
        }

        return static::$container;
    }
 }
