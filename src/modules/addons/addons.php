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

if ( ! class_exists( 'PP_Addons' ) ) {
    /**
     * class PP_Addons
     * Threaded commenting in the admin for discussion between writers and editors
     *
     * @author batmoo
     */
    class PP_Addons extends PP_Module {
        /**
         * The name of the module
         */
        const NAME = 'addons';

        /**
         * The settings slug
         */
        const SETTINGS_SLUG = 'pp-addons';

        /**
         * Twig instance
         *
         * @var Twig
         */
        protected $twig;

        /**
         * Flag for debug
         *
         * @var boolean
         */
        protected $debug = false;

        /**
         * The constructor method
         */
        public function __construct() {
            $this->module_url = $this->get_module_url( __FILE__ );

            // Register the module with PublishPress
            $args = array(
                'title'                => __( 'Add-ons', 'publishpress' ),
                'short_description'    => __( 'Pro Add-ons for PublishPress', 'publishpress' ),
                'extended_description' => false,
                'module_url'           => $this->module_url,
                'icon_class'           => 'dashicons dashicons-admin-settings',
                'slug'                 => static::NAME,
                'default_options'      => array(
                    'enabled' => 'on',
                ),
                'configure_page_cb'   => 'print_configure_view',
                'autoload'            => true,
                'options_page'        => true,
            );

            $this->module = PublishPress()->register_module( static::NAME, $args );

            // Load Twig
            $loader = new Twig_Loader_Filesystem( __DIR__ . '/twig' );
            $this->twig = new Twig_Environment( $loader, array(
                'debug' => $this->debug,
            ) );

            if ( $this->debug ) {
                $this->twig->addExtension( new Twig_Extension_Debug() );
            }
        }

        /**
         * Initialize the rest of the stuff in the class if the module is active
         */
        public function init() {
            add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
        }

        /**
         * Load any of the admin scripts we need but only on the pages we need them
         */
        public function add_admin_scripts() {
            wp_enqueue_style( 'publishpress-addons-css', $this->module_url . 'lib/addons.css', false, PUBLISHPRESS_VERSION, 'all' );
        }

        /**
         * Returns true if the plugin is active
         *
         * @param  string   $plugin
         *
         * @return boolean
         */
        protected function is_plugin_active( $plugin ) {
            return is_plugin_active( "{$plugin}/{$plugin}.php" );
        }

        /**
         * Returns true if the plugin is installed
         *
         * @param  string   $plugin
         *
         * @return boolean
         */
        protected function is_plugin_installed( $plugin ) {
            return file_exists( plugin_dir_path( PUBLISHPRESS_ROOT ) . "{$plugin}/{$plugin}.php" );
        }

        /**
         * Settings page for editorial comments
         *
         * @since 0.7
         */
        public function print_configure_view() {
            global $publishpress;

            $countEnabled    = 0;
            $icons_base_path = plugins_url( 'publishpress' ) . '/modules/addons/lib/img/';

            $addons = array(
                'publishpress-content-checklist' => array(
                    'title'       => __( 'Content Checklist', 'publishpress' ),
                    'description' => __( 'Allows PublishPress teams to define tasks that must be complete before content is published.' ),
                    'available'   => true,
                    'installed'   => $this->is_plugin_installed( 'publishpress-content-checklist' ),
                    'active'      => $this->is_plugin_active( 'publishpress-content-checklist' ),
                ),
                'publishpress-slack' => array(
                    'title'       => __( 'Slack support', 'publishpress' ),
                    'description' => __( 'PublishPress with Slack, so you can get comment and status change notifications directly on Slack.' ),
                    'available'   => true,
                    'installed'   => $this->is_plugin_installed( 'publishpress-slack' ),
                    'active'      => $this->is_plugin_active( 'publishpress-slack' ),
                ),
                'publishpress-woocommerce-checklist' => array(
                    'title'       => __( 'WooCommerce Checklist', 'publishpress' ),
                    'description' => __( 'This add-on allows WooCommerce teams to define tasks that must be complete before products are published.' ),
                    'available'   => false,
                ),
                'publishpress-multiple-authors' => array(
                    'title'       => __( 'Multiple authors support', 'publishpress' ),
                    'description' => __( 'Allows you choose multiple authors for a single post. This add-on is ideal for teams who write collabratively.' ),
                    'available'   => false,
                ),
                'publishpress-multi-site' => array(
                    'title'       => __( 'Multi-site and Multiple support', 'publishpress' ),
                    'description' => __( 'Enables PublishPress to support multiple WordPress sites. Write on one site, but publish to many sites.' ),
                    'available'   => false,
                ),
                'publishpress-zapier' => array(
                    'title'       => __( 'Zapier support', 'publishpress' ),
                    'description' => __( 'Integrates PublishPress with Zapier, so you can send comment and status changes notifications directly to Zapier.' ),
                    'available'   => false,
                ),
                'publishpress-advanced-permissions' => array(
                    'title'       => __( 'Advanced Permissions', 'publishpress' ),
                    'description' => __( 'Allows you to control which users can complete certain tasks, such as publishing content.' ),
                    'available'   => false,
                ),
            );

            $args = array(
                'addons'          => $addons,
                'icons_base_path' => $icons_base_path,
                'labels'          => array(
                    'active'         => __( 'Active', 'publishpress' ),
                    'installed'      => __( 'Installed', 'publishpress' ),
                    'get_pro_addons' => __( 'Get Pro Add-ons!', 'publishpress' ),
                    'coming_soon'    => __( 'Coming soon', 'publishpress' ),
                ),
            );

            echo $this->twig->render( 'list-of-addons.twig', $args );
        }
    }
}
