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

class Plugin
{
    // Unique identified added as a prefix to all options
    public $options_group      = 'publishpress_';
    public $options_group_name = 'publishpress_options';

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->setup_globals();
        $this->setup_actions();
    }

    public function init()
    {
        add_action('plugins_loaded', array($this, 'getInstance'));
    }

    public function getInstance()
    {
        return $this;
    }

    private function setup_globals()
    {
        $this->modules = new stdClass();
    }

    /**
     * Include the common resources to PublishPress and dynamically load the modules
     */
    private function load_modules()
    {

        // We use the WP_List_Table API for some of the table gen
        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        // PublishPress base module
        require_once(PUBLISHPRESS_ROOT_PATH . '/common/php/class-module.php');

        // Scan the modules directory and include any modules that exist there
        // $module_dirs = scandir( . '/modules/');
        $module_dirs = array(
            'calendar',
            'editorial-metadata',
            'notifications',
            'story-budget',
            'custom-status',
            'user-groups',

            // @TODO: Move for settings, and remove after cleanup
            'dashboard',
            'editorial-comments',
            'settings',
        );

        $class_names = array();
        foreach ($module_dirs as $module_dir) {
            if (file_exists(PUBLISHPRESS_ROOT_PATH . "/modules/{$module_dir}/$module_dir.php")) {
                include_once(PUBLISHPRESS_ROOT_PATH . "/modules/{$module_dir}/$module_dir.php");
                // Prepare the class name because it should be standardized
                $tmp        = explode('-', $module_dir);
                $class_name = '';
                $slug_name  = '';
                foreach ($tmp as $word) {
                    $class_name .= ucfirst($word) . '_';
                    $slug_name .= $word . '_';
                }
                $slug_name               = rtrim($slug_name, '_');
                $class_names[$slug_name] = 'PP_' . rtrim($class_name, '_');
            }
        }

        // Instantiate PP_Module as $helpers for back compat and so we can
        // use it in this class
        $this->helpers = new PP_Module();

        // Other utils
        require_once(PUBLISHPRESS_ROOT_PATH . '/common/php/util.php');

        // Instantiate all of our classes onto the PublishPress object
        // but make sure they exist too
        foreach ($class_names as $slug => $class_name) {
            if (class_exists($class_name)) {
                $this->$slug = new $class_name();
            }
        }

        // Supplementary plugins can hook into this, include their own modules
        // and add them to the $publishpress object
        do_action('pp_modules_loaded');
    }

    /**
     * Setup the default hooks and actions
     *
     * @since PublishPress 0.7.4
     * @access private
     * @uses add_action() To add various actions
     */
    private function setup_actions()
    {
        add_action('init', array($this, 'action_init'));
        add_action('init', array($this, 'action_init_after'), 1000);

        add_action('admin_init', array($this, 'action_admin_init'));

        do_action_ref_array('publishpress_after_setup_actions', array(&$this));
    }

    /**
     * Inititalizes the PublishPresss!
     * Loads options for each registered module and then initializes it if it's active
     */
    public function action_init()
    {
        load_plugin_textdomain('publishpress', null, dirname(plugin_basename(__FILE__)) . '/languages/');

        $this->load_modules();

        // Load all of the module options
        $this->load_module_options();

        // Load all of the modules that are enabled.
        // Modules won't have an options value if they aren't enabled
        foreach ($this->modules as $mod_name => $mod_data) {
            if (isset($mod_data->options->enabled) && $mod_data->options->enabled == 'on') {
                $this->$mod_name->init();
            }
        }

        do_action('pp_init');
    }

    /**
     * Initialize the plugin for the admin
     */
    public function action_admin_init()
    {

        // Upgrade if need be but don't run the upgrade if the plugin has never been used
        $previous_version = get_option($this->options_group . 'version');
        if ($previous_version && version_compare($previous_version, PUBLISHPRESS_VERSION, '<')) {
            foreach ($this->modules as $mod_name => $mod_data) {
                if (method_exists($this->$mod_name, 'upgrade')) {
                    $this->$mod_name->upgrade($previous_version);
                }
            }
            update_option($this->options_group . 'version', PUBLISHPRESS_VERSION);
        } elseif (!$previous_version) {
            update_option($this->options_group . 'version', PUBLISHPRESS_VERSION);
        }

        // For each module that's been loaded, auto-load data if it's never been run before
        foreach ($this->modules as $mod_name => $mod_data) {
            // If the module has never been loaded before, run the install method if there is one
            if (!isset($mod_data->options->loaded_once) || !$mod_data->options->loaded_once) {
                if (method_exists($this->$mod_name, 'install')) {
                    $this->$mod_name->install();
                }
                $this->update_module_option($mod_name, 'loaded_once', true);
            }
        }

        $this->register_scripts_and_styles();
    }

    /**
     * Register a new module with PublishPress
     */
    public function register_module($name, $args = array())
    {

        // A title and name is required for every module
        if (!isset($args['title'], $name)) {
            return false;
        }

        $defaults = array(
            'title'                => '',
            'short_description'    => '',
            'extended_description' => '',
            'icon_class'           => 'dashicons dashicons-admin-generic',
            'slug'                 => '',
            'post_type_support'    => '',
            'default_options'      => array(),
            'options'              => false,
            'configure_page_cb'    => false,
            'configure_link_text'  => __('Configure', 'publishpress'),
            // These messages are applied to modules and can be overridden if custom messages are needed
            'messages'             => array(
                'settings-updated'    => __('Settings updated.', 'publishpress'),
                'form-error'          => __('Please correct your form errors below and try again.', 'publishpress'),
                'nonce-failed'        => __('Cheatin&#8217; uh?', 'publishpress'),
                'invalid-permissions' => __('You do not have necessary permissions to complete this action.', 'publishpress'),
                'missing-post'        => __('Post does not exist', 'publishpress'),
            ),
            'autoload'             => false, // autoloading a module will remove the ability to enable or disable it
        );
        if (isset($args['messages'])) {
            $args['messages'] = array_merge((array)$args['messages'], $defaults['messages']);
        }
        $args                       = array_merge($defaults, $args);
        $args['name']               = $name;
        $args['options_group_name'] = $this->options_group . $name . '_options';

        if (!isset($args['settings_slug'])) {
            $args['settings_slug'] = 'pp-' . $args['slug'] . '-settings';
        }

        if (empty($args['post_type_support'])) {
            $args['post_type_support'] = 'pp_' . $name;
        }

        // If there's a Help Screen registered for the module, make sure we
        // auto-load it
        if (!empty($args['settings_help_tab'])) {
            add_action('load-publishpress_page_' . $args['settings_slug'], array(&$this->$name, 'action_settings_help_menu'));
        }

        $this->modules->$name = (object) $args;
        do_action('pp_module_registered', $name);

        return $this->modules->$name;
    }

    /**
     * Load all of the module options from the database
     * If a given option isn't yet set, then set it to the module's default (upgrades, etc.)
     */
    public function load_module_options()
    {
        foreach ($this->modules as $mod_name => $mod_data) {
            $this->modules->$mod_name->options = get_option($this->options_group . $mod_name . '_options', new stdClass);
            foreach ($mod_data->default_options as $default_key => $default_value) {
                if (!isset($this->modules->$mod_name->options->$default_key)) {
                    $this->modules->$mod_name->options->$default_key = $default_value;
                }
            }

            $this->$mod_name->module = $this->modules->$mod_name;
        }

        do_action('pp_module_options_loaded');
    }

    /**
     * Load the post type options again so we give add_post_type_support() a chance to work
     *
     * @see https://pressshack.com/2011/11/17/publishpress-v0-7-alpha2-notes/#comment-232
     */
    public function action_init_after()
    {
        foreach ($this->modules as $mod_name => $mod_data) {
            if (isset($this->modules->$mod_name->options->post_types)) {
                $this->modules->$mod_name->options->post_types = $this->helpers->clean_post_type_options($this->modules->$mod_name->options->post_types, $mod_data->post_type_support);
            }

            $this->$mod_name->module = $this->modules->$mod_name;
        }
    }

    /**
     * Get a module by one of its descriptive values
     */
    public function get_module_by($key, $value)
    {
        $module = false;
        foreach ($this->modules as $mod_name => $mod_data) {
            if ($key == 'name' && $value == $mod_name) {
                $module =  $this->modules->$mod_name;
            } else {
                foreach ($mod_data as $mod_data_key => $mod_data_value) {
                    if ($mod_data_key == $key && $mod_data_value == $value) {
                        $module = $this->modules->$mod_name;
                    }
                }
            }
        }

        return $module;
    }

    /**
     * Update the $publishpress object with new value and save to the database
     */
    public function update_module_option($mod_name, $key, $value)
    {
        $this->modules->$mod_name->options->$key = $value;
        $this->$mod_name->module                 = $this->modules->$mod_name;

        return update_option($this->options_group . $mod_name . '_options', $this->modules->$mod_name->options);
    }

    public function update_all_module_options($mod_name, $new_options)
    {
        if (is_array($new_options)) {
            $new_options = (object)$new_options;
        }

        $this->modules->$mod_name->options = $new_options;
        $this->$mod_name->module           = $this->modules->$mod_name;

        return update_option($this->options_group . $mod_name . '_options', $this->modules->$mod_name->options);
    }

    /**
     * Registers commonly used scripts + styles for easy enqueueing
     */
    public function register_scripts_and_styles()
    {
        wp_enqueue_style('pp-admin-css', PUBLISHPRESS_URL . 'common/css/publishpress-admin.css', false, PUBLISHPRESS_VERSION, 'all');

        wp_register_script('jquery-listfilterizer', PUBLISHPRESS_URL . 'common/js/jquery.listfilterizer.js', array('jquery'), PUBLISHPRESS_VERSION, true);
        wp_register_style('jquery-listfilterizer', PUBLISHPRESS_URL . 'common/css/jquery.listfilterizer.css', false, PUBLISHPRESS_VERSION, 'all');

        wp_register_script('jquery-quicksearch', PUBLISHPRESS_URL . 'common/js/jquery.quicksearch.js', array('jquery'), PUBLISHPRESS_VERSION, true);

        // @compat 3.3
        // Register jQuery datepicker plugin if it doesn't already exist. Datepicker plugin was added in WordPress 3.3
        global $wp_scripts;
        if (!isset($wp_scripts->registered['jquery-ui-datepicker'])) {
            wp_register_script('jquery-ui-datepicker', PUBLISHPRESS_URL . 'common/js/jquery.ui.datepicker.min.js', array('jquery', 'jquery-ui-core'), '1.8.16', true);
        }
    }
}
