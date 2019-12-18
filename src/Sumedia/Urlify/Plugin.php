<?php

namespace Sumedia\Urlify;

class Plugin
{
    public function init()
    {
        add_action('plugins_loaded', [$this, 'textdomain']);
        add_action('admin_print_styles', [$this, 'admin_stylesheets']);
        add_action('admin_menu', [$this, 'setup_menu']);
        $this->factories();
        $this->rewrite_listener();
        add_filter('script_loader_src', [$this, 'urlify']);
        add_filter('style_loader_src', [$this, 'urlify']);
        add_filter('site_url', [$this, 'urlify']);
        add_filter('login_url', [$this, 'urlify']);
        add_filter('admin_url', [$this, 'urlify']);
        add_action('plugins_loaded', [$this, 'controller']);
    }

    function activate()
    {
        $installer = new \Sumedia\Urlify\Db\Installer;
        $installer->install();

        $urls = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Repository\Urls');
        $admin_url = $urls->get_admin_url();
        $login_url = $urls->get_login_url();

        $htaccess = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Htaccess');
        $htaccess->write($admin_url, $login_url);

        $config = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Config');
        $config->write($admin_url);

        add_action('admin_init', function(){
            wp_redirect(admin_url('plugins.php?' . $_SERVER['QUERY_STRING']));
        });
    }

    function deactivate()
    {
        $urls = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Repository\Urls');
        $admin_url = $urls->get_admin_url();
        $login_url = $urls->get_login_url();

        $htaccess = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Htaccess');
        $htaccess->register_rewrite_filter();
        $htaccess->remove();

        $config = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Config');
        $config->remove();

        // can't redirect here =/ - user drops to 404
    }

    function textdomain()
    {
        load_plugin_textdomain(
            SUMEDIA_URLIFY_PLUGIN_NAME,
            false,
            SUMEDIA_URLIFY_PLUGIN_NAME . DIRECTORY_SEPARATOR . 'languages'
        );
    }

    public function admin_stylesheets()
    {
        $cssFile = SUMEDIA_URLIFY_PLUGIN_URL . '/assets/css/admin-style.css';
        wp_enqueue_style('sumedia_urlify_admin_style', $cssFile);
    }

    public function setup_menu()
    {
        $menu = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Admin\View\Menu');
        add_submenu_page(
            'plugins.php',
            $menu->get_page_title(),
            $menu->build_iconified_title(),
            'manage_options',
            $menu->get_slug(),
            [$menu, 'render'],
            $menu->get_pos()
        );
    }

    public function factories()
    {
        $factory = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\HtaccessFactory');
        \Sumedia\Urlify\Base\Registry::set_factory('Sumedia\Urlify\Htaccess', $factory);
    }

    public function rewrite_listener()
    {
        \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Htaccess');
    }

    public function controller()
    {
        if (isset($_GET['page']) && $_GET['page'] == SUMEDIA_URLIFY_PLUGIN_NAME) {

            $action = isset($_POST['action']) ? $_POST['action'] : null;
            $action = null == $action && isset($_GET['action']) ? $_GET['action'] : $action;
            if (!preg_match('#^[a-z0-9_\-]+$#i', $action)) {
                return;
            }

            $controller = 'Sumedia\Urlify\Admin\Controller\\' . $action;

            $check_file = SUMEDIA_URLIFY_PLUGIN_PATH . DS . 'src' . DS . str_replace('\\', DS, $controller) . '.php';

            if (file_exists($check_file)) {
                $controller = \Sumedia\Urlify\Base\Registry::get($controller);
                add_action('admin_init', [$controller, 'prepare']);
                add_action('admin_init', [$controller, 'execute']);
            }

        }
    }

    public function urlify($url)
    {
        $urls = \Sumedia\Urlify\Base\Registry::get('Sumedia\Urlify\Repository\Urls');
        $admin_url = $urls->get_admin_url();
        $login_url = $urls->get_login_url();

        return str_replace(
            ['wp-login.php', '/wp-admin/'],
            [$login_url, '/' . $admin_url . '/'],
            $url
        );
    }
}