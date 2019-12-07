<?php

/**
 * Sumedia URL Changer
 *
 * @package     Sumedia_Urlify
 * @copyright   Copyright (C) 2019, Sumedia - kontakt@sumedia-webdesign.de
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Sumedia URL Changer
 * Version:     0.1.0
 * Description: Changes important URL's to improve security
 * Author:      Sven Ullmann
 * Author URI:  https://www.sumedia-webdesign.de
 * Text Domain: sumedia-urlify
 * Domain Path: /languages/
 * License:     GPL v3
 *
 * WC requires at least: 3.0
 * WC tested up to: 3.8
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
 */

if (!function_exists( 'add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

if (!defined('SUMEDIA_BASE_VERSION')) {
    if (!func_exists('sumedia_base_plugin_missing_message')) {
        function sumedia_base_plugin_missing_message()
        {
            return print '<div id="message" class="error fade"><p>' . __('In order to use Sumedia Plugins you need to install Sumedia Base Plugin (sumedia-base).') . '</p></div>';
        }
        add_action('admin_notices', 'sumedia_base_plugin_missing_messsage');
    }
} else {

    function sumedia_urlify_initialize()
    {
        if (defined('SUMEDIA_URLIFY_VERSION')) {
            return;
        }

        global $wpdb;

        define('SUMEDIA_URLIFY_VERSION', '0.1.0');
        define('SUMEDIA_URLIFY_PLUGIN_NAME', dirname(plugin_basename(__FILE__)));

        require_once(__DIR__ . '/vendor/autoload.php');

        $autoloader = Sumedia_Base_Autoloader::get_instance();
        $autoloader->register_autoload_dir(SUMEDIA_URLIFY_PLUGIN_NAME, 'inc');
        $autoloader->register_autoload_dir(SUMEDIA_URLIFY_PLUGIN_NAME, 'admin/view');

        $event = new Sumedia_Base_Event(function () {
            load_plugin_textdomain(
                'sumedia-urlify',
                false,
                SUMEDIA_URLIFY_PLUGIN_NAME . '/languages/');
        });
        add_action('plugins_loaded', [$event, 'execute']);

        $installer = new Sumedia_Urlify_Installer;
        register_activation_hook(__FILE__, [$installer, 'install']);

        $deactivator = new Sumedia_Urlify_Deactivator;
        register_deactivation_hook( __FILE__, [$deactivator, 'deactivate'] );

        $registry = Sumedia_Base_Registry::get_instance();
        $view_renderer = $registry->get('view_renderer');

        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'sumedia' && isset($_REQUEST['plugin']) && $_REQUEST['plugin'] = 'urlify') {
            $view_renderer->set_template(SUMEDIA_PLUGIN_PATH . SUMEDIA_URLIFY_PLUGIN_NAME . '/admin/templates/config.phtml');
        }

        $form = new Sumedia_Urlify_Config_Form();
        $form->load();
        $data = $form->get_data();

        $registry->set('urlify_config_form', $form);

        $urls = new Sumedia_Urlify_Admin_View_Config();
        foreach ($data as $urltype => $url) {
            $urls->set($urltype, $url);
        }
        $view_renderer->set('urls', $urls);

        $plugins = $view_renderer->get('plugins');
        $plugins->set_plugin(SUMEDIA_URLIFY_PLUGIN_NAME, [
            'description_template' => __DIR__ . '/admin/templates/plugin.phtml',
            'options' => [
                'config_link' => admin_url('admin.php?page=sumedia&plugin=urlify')
            ]
        ]);

        $event = new Sumedia_Base_Event(function(){
            if (isset($_GET['action']) && $_GET['action'] == 'set-config') {
                $registry = Sumedia_Base_Registry::get_instance();
                $form = $registry->get('urlify_config_form');
                $form->do_request($_POST);
                $form->save();
                wp_redirect(admin_url('admin.php?page=sumedia&plugin=urlify'));
            }
        });
        add_action('init', [$event, 'execute']);

        add_filter('script_loader_src','sumedia_urlify_url');
        add_filter('style_loader_src', 'sumedia_urlify_url');
        add_filter('site_url', 'sumedia_urlify_url');
        add_filter('login_url', 'sumedia_urlify_url');
        add_filter('admin_url', 'sumedia_urlify_url');
        function sumedia_urlify_url($url)
        {
            $registry = Sumedia_Base_Registry::get_instance();
            $form = $registry->get('urlify_config_form');
            $data = $form->get_data();
            return str_replace(
                ['wp-login.php', '/wp-admin/'],
                [$data['login_url'], '/' . $data['admin_url'] . '/'],
                $url
            );
        }
    }
    sumedia_urlify_initialize();
}