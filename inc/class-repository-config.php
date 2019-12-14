<?php

require_once(dirname(__DIR__) . str_replace('/', DIRECTORY_SEPARATOR, '/vendor/wp-cli/wp-config-transformer/src/WPConfigTransformer.php'));

class Sumedia_Urlify_Repository_Config
{
    /**
     * @var $this
     */
    protected static $instance;

    /**
     * Sumedia_Urlify_Repository_Config constructor.
     */
    protected function __construct(){}

    /**
     * @return Sumedia_Urlify_Repository_Htaccess
     */
    public static function get_instance()
    {
        if (null == static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

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