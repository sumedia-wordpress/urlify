<?php

class Sumedia_Urlify_Configure_WPConfig
{
    public function write($admin_url)
    {
        $path = $this->get_wp_path() . '/' . $admin_url;
        $wp_config = new WPConfigTransformer(get_home_path() . '/wp-config.php');
        $wp_config->update('constant', 'ADMIN_COOKIE_PATH', $path);
    }

    public function get_wp_path()
    {
        return parse_url(get_bloginfo('url'), PHP_URL_PATH);
    }
}
