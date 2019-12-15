<?php

class Sumedia_Urlify_Htaccess_Factory extends Sumedia_Base_Factory
{
    public function build()
    {
        $urls = Sumedia_Base_Registry::get('Sumedia_Urlify_Repository_Urls');
        $admin_url = $urls->get_admin_url();
        $login_url = $urls->get_login_url();
        return new Sumedia_Urlify_Htaccess($admin_url, $login_url);
    }
}