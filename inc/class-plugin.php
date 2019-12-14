<?php

class Sumedia_Urlify_Plugin
{
    public function init()
    {
        add_action('plugins_loaded', [$this, 'textdomain']);

        $this->rewrite_listener();
        $this->filter_url_functions();

        add_action('plugins_loaded', [$this, 'plugin_view']);
        add_action('plugins_loaded', [$this, 'controller']);
    }

    function activate()
    {
        $installer = new Sumedia_Urlify_Db_Installer();
        $installer->install();

        $urls = Sumedia_Urlify_Repository_Urls::get_instance();
        $admin_url = $urls->get_admin_url();
        $login_url = $urls->get_login_url();

        $htaccess = Sumedia_Urlify_Repository_Htaccess::get_instance($admin_url, $login_url);
        $htaccess->write($admin_url, $login_url);

        $config = Sumedia_Urlify_Repository_Config::get_instance();
        $config->write($admin_url);

        add_action('admin_init', function(){
            wp_redirect(admin_url('plugins.php?' . $_SERVER['QUERY_STRING']));
        });
    }

    function deactivate()
    {
        $urls = Sumedia_Urlify_Repository_Urls::get_instance();
        $admin_url = $urls->get_admin_url();
        $login_url = $urls->get_login_url();

        $htaccess = Sumedia_Urlify_Repository_Htaccess::get_instance($admin_url, $login_url);
        $htaccess->register_rewrite_filter();
        $htaccess->remove();

        $config = Sumedia_Urlify_Repository_Config::get_instance();
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

    public function rewrite_listener()
    {
        $urls = Sumedia_Urlify_Repository_Urls::get_instance();
        $admin_url = $urls->get_admin_url();
        $login_url = $urls->get_login_url();

        Sumedia_Urlify_Repository_Htaccess::get_instance($admin_url, $login_url);
    }

    public function plugin_view()
    {
        $plugins = Sumedia_Base_Registry_View::get('Sumedia_Base_Admin_View_Plugins');
        $plugins->add_plugin(SUMEDIA_URLIFY_PLUGIN_NAME, [
            'name' => 'Urlify',
            'version' => SUMEDIA_URLIFY_VERSION,
            'options' => [
                [
                    'name' => __('Configuration', SUMEDIA_URLIFY_PLUGIN_NAME),
                    'url' => admin_url('admin.php?page=sumedia&plugin=' . SUMEDIA_URLIFY_PLUGIN_NAME . '&action=config')
                ]
            ],
            'description_template' => Suma\ds(SUMEDIA_PLUGIN_PATH . SUMEDIA_URLIFY_PLUGIN_NAME . '/admin/templates/plugin.phtml')
        ]);
    }

    public function controller()
    {
        if (isset($_GET['page']) && isset($_GET['plugin']) && isset($_GET['action'])) {
            if ($_GET['page'] == 'sumedia' && $_GET['plugin'] == SUMEDIA_URLIFY_PLUGIN_NAME)
            {
                if ($_GET['action'] == 'config') {
                    $controller = Sumedia_Urlify_Admin_Controller_Config::get_instance();
                } elseif ($_GET['action'] == 'setconfig') {
                    $controller = Sumedia_Urlify_Admin_Controller_Setconfig::get_instance();
                }

                if (isset($controller)) {
                    add_action('admin_init', [$controller, 'prepare']);
                    add_action('admin_init', [$controller, 'execute']);
                }
            }
        }
    }

    public function filter_url_functions()
    {
        add_filter('script_loader_src','sumedia_urlify_url');
        add_filter('style_loader_src', 'sumedia_urlify_url');
        add_filter('site_url', 'sumedia_urlify_url');
        add_filter('login_url', 'sumedia_urlify_url');
        add_filter('admin_url', 'sumedia_urlify_url');
        function sumedia_urlify_url($url)
        {
            $urls = Sumedia_Urlify_Repository_Urls::get_instance();
            $admin_url = $urls->get_admin_url();
            $login_url = $urls->get_login_url();

            return str_replace(
                ['wp-login.php', '/wp-admin/'],
                [$login_url, '/' . $admin_url . '/'],
                $url
            );
        }
    }
}