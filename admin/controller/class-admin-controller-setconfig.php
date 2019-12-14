<?php

class Sumedia_Urlify_Admin_Controller_Setconfig extends Sumedia_Base_Controller
{
    /**
     * @var $this
     */
    protected static $instance;

    public function execute()
    {
        $form = Sumedia_Base_Registry_Form::get('Sumedia_Urlify_Admin_Form_Config');
        $urls = Sumedia_Urlify_Repository_Urls::get_instance();
        $messenger = Sumedia_Base_Messenger::get_instance();

        if ($form->is_valid($_POST)) {
            $urls->set_admin_url($form->get_data('admin_url'));
            $urls->set_login_url($form->get_data('login_url'));

            $htaccess = Sumedia_Urlify_Repository_Htaccess::get_instance($form->get_data('admin_url'), $form->get_data('login_url'));
            $htaccess->write($form->get_data('admin_url'), $form->get_data('login_url'));

            $config = Sumedia_Urlify_Repository_Config::get_instance();
            $config->write($form->get_data('admin_url'));

            $messenger->add_message($messenger::TYPE_SUCCESS, __('The data has successfully been saved', SUMEDIA_URLIFY_PLUGIN_NAME));
        } else {
            $messenger->add_message($messenger::TYPE_ERROR, __('The data could not be setted', SUMEDIA_URLIFY_PLUGIN_NAME));
        }

        wp_redirect(admin_url('admin.php?page=sumedia&plugin=' . SUMEDIA_URLIFY_PLUGIN_NAME . '&action=config'));
        exit;
    }
}
