<?php

require_once(dirname(__DIR__) . str_replace('/', DIRECTORY_SEPARATOR, '/vendor/wp-cli/wp-config-transformer/src/WPConfigTransformer.php'));

class Sumedia_Urlify_Config
{
    /**
     * @param string $admin_url
     * @throws Exception
     */
    public function write($admin_url)
    {
        $path = $this->get_wp_path() . DIRECTORY_SEPARATOR . $admin_url;
        $wp_config = new WPConfigTransformer(get_home_path() . DIRECTORY_SEPARATOR . 'wp-config.php');
        $wp_config->update('constant', 'ADMIN_COOKIE_PATH', $path);
    }

    public function remove()
    {
        $wp_config = new WPConfigTransformer(get_home_path() . DIRECTORY_SEPARATOR . 'wp-config.php');
        $wp_config->remove('constant', 'ADMIN_COOKIE_PATH');
    }

    /**
     * @return string
     */
    protected function get_wp_path()
    {
        return parse_url(get_bloginfo('url'), PHP_URL_PATH);
    }
}