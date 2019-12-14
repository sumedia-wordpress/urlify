<?php

class Sumedia_Urlify_Admin_View_Config extends Sumedia_Base_View
{
    public function __construct()
    {
        $this->template = Suma\ds(SUMEDIA_PLUGIN_PATH . '/' . SUMEDIA_URLIFY_PLUGIN_NAME . '/admin/templates/config.phtml');
        $form = new Sumedia_Urlify_Admin_Form_Config;
        $form->set_data([
            'admin_url' => 'wp-admin',
            'login_url' => 'wp-login.php'
        ]);
        $this->set('form', $form);
    }
}