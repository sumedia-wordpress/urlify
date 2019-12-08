<?php

class Sumedia_Urlify_Plugin
{
    public function init()
    {
        $this->url_config_form();
        $this->view();
        $this->post_set_config();
        $this->filter_url_functions();
    }

    public function url_config_form()
    {
        $registry = Sumedia_Base_Registry::get_instance();
        $form = new Sumedia_Urlify_Config_Form();
        $form->load();
        $registry->set('sumedia_urlify_config_form', $form);
    }

    public function view()
    {
        $view = Sumedia_Base_Registry::get_instance('view');

        $plugins = $view->get('sumedia_base_admin_view_plugins');
        $plugins->plugins[SUMEDIA_URLIFY_PLUGIN_NAME] = [
            'description_template' => Suma\ds(SUMEDIA_PLUGIN_PATH . SUMEDIA_URLIFY_PLUGIN_NAME . '/admin/templates/plugin.phtml')
        ];

        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'sumedia' && isset($_REQUEST['plugin']) && $_REQUEST['plugin'] == 'urlify') {
            $view->get('sumedia_base_admin_view_menu')->template = Suma\ds(SUMEDIA_PLUGIN_PATH . SUMEDIA_URLIFY_PLUGIN_NAME . '/admin/templates/config.phtml');

            $heading = $view->get('sumedia_base_admin_view_heading');
            $heading->title = __('Urlify');
            $heading->side_title = __('Configuration');
            $heading->version = SUMEDIA_URLIFY_VERSION;
        }

        $registry = Sumedia_Base_Registry::get_instance();
        $url_config_form = $registry->get('sumedia_urlify_config_form');
        $data = $url_config_form->get_data();
        $urls = new Sumedia_Urlify_Admin_View_Config();
        foreach ($data as $urltype => $url) {
            $urls->set($urltype, $url);
        }
        $view->set('urls', $urls);
    }

    public function post_set_config()
    {
        $event = new Sumedia_Base_Event(function(){
            if (isset($_GET['action']) && $_GET['action'] == 'set-config') {
                $registry = Sumedia_Base_Registry::get_instance();
                $form = $registry->get('urlify_config_form');
                $form->do_request($_POST);
                $form->save();
                $event = new Sumedia_Base_Event(function() {
                    wp_redirect(admin_url('admin.php?page=sumedia&plugin=urlify'));
                });
                add_action('template_redirect', [$event, 'execute']);
            }
        });
        add_action('init', [$event, 'execute']);
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
            $registry = Sumedia_Base_Registry::get_instance();
            $form = $registry->get('sumedia_urlify_config_form');
            $data = $form->get_data();
            return str_replace(
                ['wp-login.php', '/wp-admin/'],
                [$data['login_url'], '/' . $data['admin_url'] . '/'],
                $url
            );
        }
    }
}