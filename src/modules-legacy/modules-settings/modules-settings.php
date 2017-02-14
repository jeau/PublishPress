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

if (!class_exists('PP_Modules_Settings')) {
    /**
     * class PP_Modules_Settings
     * Threaded commenting in the admin for discussion between writers and editors
     *
     * @author batmoo
     */
    class PP_Modules_Settings extends PP_Module
    {

        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);
            // Register the module with PublishPress
            $args = array(
                'title'                => __('Settings', 'publishpress'),
                'short_description'    => __('PublishPress is the essential plugin for any site with multiple writers', 'publishpress'),
                'extended_description' => false,
                'module_url'           => $this->module_url,
                'icon_class'           => 'dashicons dashicons-admin-comments',
                'slug'                 => 'modules-settings',
                'default_options'      => array(
                    'enabled'    => 'on',
                ),
                'configure_page_cb'   => 'print_configure_view',
                'autoload'            => false,
                'options_page'        => true,
                'add_menu'            => true,
            );

            $this->module = PublishPress()->register_module('modules_settings', $args);
        }

        /**
         * Initialize the rest of the stuff in the class if the module is active
         */
        public function init()
        {
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_init', array($this, 'helper_settings_validate_and_save'), 100);
            add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'));
        }

        /**
         * Load any of the admin scripts we need but only on the pages we need them
         */
        public function add_admin_scripts()
        {
            wp_enqueue_script('publishpress-post_comment', $this->module_url . 'lib/modules-settings.js', array('jquery', 'post'), PUBLISHPRESS_VERSION, true);
            wp_enqueue_style('publishpress-modules-css', $this->module_url . 'lib/modules-settings.css', false, PUBLISHPRESS_VERSION, 'all');
        }

        /**
         * Register settings for notifications so we can partially use the Settings API
         * (We use the Settings API for form generation, but not saving)
         *
         * @since 0.7
         * @uses add_settings_section(), add_settings_field()
         */
        public function register_settings()
        {

        }

        /**
         * Validate data entered by the user
         *
         * @since 0.7
         *
         * @param array $new_options New values that have been entered by the user
         * @return array $new_options Form values after they've been sanitized
         */
        public function settings_validate($new_options)
        {

        }

        /**
         * Settings page for editorial comments
         *
         * @since 0.7
         */
        public function print_configure_view()
        {
            global $publishpress;

            ?>
            <form class="basic-settings" action="<?php echo esc_url(menu_page_url($this->module->settings_slug, false)); ?>" method="post">
                <?php settings_fields($this->module->options_group_name); ?>
                <?php do_settings_sections($this->module->options_group_name); ?>
                <?php echo '<input id="publishpress_module_name" name="publishpress_module_name" type="hidden" value="' . esc_attr($this->module->name) . '" />'; ?>

                <div id="modules-wrapper">
                <?php
                foreach ($publishpress->modules as $mod_name => $mod_data) {
                    if ($mod_data->autoload || $mod_data->slug === 'modules-settings') {
                        continue;
                    }
                    ?>
                    <div id="<?php echo $mod_data->slug; ?>" class="module-box <?php echo ($mod_data->options->enabled == 'on') ? 'module-enabled' : 'module-disabled'; ?>">
                        <div>
                            <?php
                            if (isset($mod_data->icon_class)) {
                                echo '<span class="' . esc_html($mod_data->icon_class) . ' module-icon"></span>';
                            }
                            ?>
                            <h4><?php echo $mod_data->title; ?></h4>

                            <span>
                                <?php
                                echo '<input type="submit" class="button-primary button enable-disable-publishpress-module"';
                                echo ' data-slug="' . $mod_data->slug . '"';
                                if ($mod_data->options->enabled == 'on') {
                                    echo ' style="display:none;"';
                                }
                                echo ' value="' . __('Enable', 'publishpress') . '" />';
                                echo '<input type="submit" class="button-secondary button-remove button enable-disable-publishpress-module"';
                                echo ' data-slug="' . $mod_data->slug . '"';
                                if ($mod_data->options->enabled == 'off') {
                                    echo ' style="display:none;"';
                                }
                                echo ' value="' . __('Disable', 'publishpress') . '" />';
                                ?>
                            </span>
                        </div>

                        <p><?php echo strip_tags($mod_data->short_description); ?></p>
                    </div>
                    <?php
                }
                ?>
                </div>
                <?php
                wp_nonce_field('change-publishpress-module-nonce', 'change-module-nonce', false);
                ?>
            </form>
            <?php

            $this->options_page_controller();
        }

        public function options_page_controller()
        {
            global $publishpress;

            $module_settings_slug = isset($_GET['module']) && !empty($_GET['module']) ? $_GET['module'] : 'pp-modules-settings-settings';
            $requested_module = $publishpress->get_module_by('settings_slug', $module_settings_slug);
            if (empty($requested_module)) {
                $requested_module = 'pp-calendar-settings';
            }

            // If there's been a message, let's display it
            if (isset($_GET['message'])) {
                $message = $_GET['message'];
            } elseif (isset($_REQUEST['message'])) {
                $message = $_REQUEST['message'];
            } elseif (isset($_POST['message'])) {
                $message = $_POST['message'];
            } else {
                $message = false;
            }
            if ($message && isset($requested_module->messages[$message])) {
                $display_text = '<span class="publishpress-updated-message publishpress-message">' . esc_html($requested_module->messages[$message]) . '</span>';
            }

            // If there's been an error, let's display it
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
            } elseif (isset($_REQUEST['error'])) {
                $error = $_REQUEST['error'];
            } elseif (isset($_POST['error'])) {
                $error = $_POST['error'];
            } else {
                $error = false;
            }
            if ($error && isset($requested_module->messages[$error])) {
                $display_text = '<span class="publishpress-error-message publishpress-message">' . esc_html($requested_module->messages[$error]) . '</span>';
            }

            ?>
            <h1><?php echo __('PublishPress', 'publishpress') . ': ' . $requested_module->title; ?></h1>
            <div class="wrap publishpress-admin">
                <div class="explanation">
                    <?php if ($requested_module->short_description): ?>
                    <h3><?php echo $requested_module->short_description; ?></h3>
                    <?php endif; ?>
                    <?php if ($requested_module->extended_description): ?>
                    <p><?php echo $requested_module->extended_description; ?></p>
                    <?php endif; ?>
                </div>

                <h2 class="nav-tab-wrapper">
                    <?php
                    foreach ($publishpress->modules as $mod_name => $mod_data) {
                        if (!isset($mod_data->options_page) || $mod_data->options_page === false || $mod_data->options->enabled !== 'on') {
                            continue;
                        }
                        ?>
                        <a
                            href="?page=pp-options&module=<?php echo $mod_data->settings_slug; ?>"
                            class="nav-tab <?php echo ($module_settings_slug == $mod_data->settings_slug) ? 'nav-tab-active' : ''; ?> "
                            >
                            <?php echo $mod_data->title ?>
                        </a>
                        <?php
                    }
                    ?>
                </h2>
                <?php
                $configure_callback    = $requested_module->configure_page_cb;
                $requested_module_name = $requested_module->name;

                $publishpress->$requested_module_name->$configure_callback();
                ?>
            </div>
            <?php
        }

        /**
         * Validation and sanitization on the settings field
         * This method is called automatically/ doesn't need to be registered anywhere
         *
         * @since 0.7
         */
        public function helper_settings_validate_and_save()
        {
            if (!isset($_POST['action'], $_POST['_wpnonce'], $_POST['option_page'], $_POST['_wp_http_referer'], $_POST['publishpress_module_name'], $_POST['submit']) || !is_admin()) {
                return false;
            }

            global $publishpress;
            $module_name = sanitize_key($_POST['publishpress_module_name']);

            if ($_POST['action'] != 'update'
                || $_POST['option_page'] != $publishpress->$module_name->module->options_group_name) {
                return false;
            }

            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], $publishpress->$module_name->module->options_group_name . '-options')) {
                wp_die(__('Cheatin&#8217; uh?'));
            }

            $new_options = (isset($_POST[$publishpress->$module_name->module->options_group_name])) ? $_POST[$publishpress->$module_name->module->options_group_name] : array();

            // Only call the validation callback if it exists?
            if (method_exists($publishpress->$module_name, 'settings_validate')) {
                $new_options = $publishpress->$module_name->settings_validate($new_options);
            }

            // Cast our object and save the data.
            $new_options = (object)array_merge((array)$publishpress->$module_name->module->options, $new_options);
            $publishpress->update_all_module_options($publishpress->$module_name->module->name, $new_options);

            // Redirect back to the settings page that was submitted without any previous messages
            $goback = add_query_arg('message', 'settings-updated',  remove_query_arg(array('message'), wp_get_referer()));
            wp_safe_redirect($goback);
            exit;
        }
    }
}
