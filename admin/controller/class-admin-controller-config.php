<?php

class Sumedia_Urlify_Admin_Controller_Config extends Sumedia_Base_Controller
{
    /**
     * @var $this
     */
    protected static $instance;

    public function prepare()
    {
        $overview = Sumedia_Base_Registry_View::get('Sumedia_Base_Admin_View_Overview');
        $overview->set_content_view(Sumedia_Base_Registry_View::get('Sumedia_Urlify_Admin_View_Config'));

        $heading = Sumedia_Base_Registry_View::get('Sumedia_Base_Admin_View_Heading');
        $heading->set_title(__('Urlify', SUMEDIA_URLIFY_PLUGIN_NAME));
        $heading->set_side_title(__('Configuration', SUMEDIA_URLIFY_PLUGIN_NAME));
        $heading->set_version(SUMEDIA_URLIFY_VERSION);
        $heading->set_options([
            [
                'name' => __('Back to the plugin overview'),
                'url' => admin_url('admin.php?page=sumedia')
            ]
        ]);
    }

    public function execute()
    {
        $form = Sumedia_Base_Registry_View::get('Sumedia_Urlify_Admin_Form_Config');
        $urls = Sumedia_Urlify_Repository_Urls::get_instance();
        $form->set_data([
            'admin_url' => $urls->get_admin_url(),
            'login_url' => $urls->get_login_url()
        ]);
    }
}