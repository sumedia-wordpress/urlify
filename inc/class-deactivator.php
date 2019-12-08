<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

class Sumedia_Urlify_Deactivator
{
    protected $table_name = 'sumedia_urlify_urls';

    public function deactivate()
    {
        $htaccess = new Sumedia_Urlify_Configure_Htaccess();
        $htaccess->remove();

        $wp_config = new Sumedia_Urlify_Configure_WPConfig();
        $wp_config->write('wp-admin');
    }
}