<?php

class Sumedia_Urlify_Admin_View_Plugin extends Sumedia_Base_View
{
    public function __construct()
    {
        $this->set_template(SUMEDIA_BASE_PATH . SUMEDIA_URLIFY_PLUGIN_NAME . Suma\ds('/admin/templates/plugin.phtml'));
    }
}