<?php

class Sumedia_Urlify_Configure_Htaccess
{
    public function remove()
    {
        $file = get_home_path() . DIRECTORY_SEPARATOR . '.htaccess';
        $content = file_get_contents($file);
        $content = preg_replace('/\# BEGIN sumedia\-urlify.*?\# END sumedia\-urlify/ims','', $content);
        file_put_contents($file, $content);
    }

    public function write($admin_url, $login_url)
    {
        $modification = $this->get_modification($admin_url, $login_url);
        $file = get_home_path() . DIRECTORY_SEPARATOR . '.htaccess';
        $content = file_get_contents($file);
        $content = preg_replace('/\# BEGIN sumedia\-urlify.*?\# END sumedia\-urlify/ims','', $content);

        $lines = explode("\n", $content);

        $in_wp = false;
        $in_rewrite = false;
        $write_into = false;
        $is_written = false;
        $newlines = [];
        foreach ($lines as $line) {
            if ('# BEGIN WordPress' == $line) {
                $in_wp = true;
            }
            if ('# END WordPress' == $line) {
                $in_wp = false;
            }
            if ($in_wp && '<IfModule mod_rewrite.c>') {
                $in_rewrite = true;
            }
            if('</IfModule>' == $line) {
                $in_rewrite = false;
            }
            if (!$is_written && $write_into) {
                foreach(explode('\n', $modification) as $_line) {
                    $newlines[] = $_line;
                }
                $write_into = false;
                $is_written = true;
            } else {
                $newlines[] = $line;
            }
            if ($in_wp && $in_rewrite && preg_match('/.*?RewriteBase.*/', $line)) {
                $write_into = true;
            }
        }

        file_put_contents($file, implode("\n", $newlines));
    }

    public function get_wp_path()
    {
        return parse_url(get_bloginfo('url'), PHP_URL_PATH);
    }

    public function get_modification($admin_url, $login_url)
    {
        return '    # BEGIN sumedia-urlify
    RewriteCond %{REQUEST_URI} ^.*?/?' . preg_quote($admin_url) . '/?.*$
    RewriteRule ^(.*?/?)' . preg_quote($admin_url) . '(/?.*)$ $1wp-admin$2 [L,E=IS_BACKEND:1,END]
    
    RewriteCond %{ENV:REDIRECT_IS_BACKEND} !^1$
    RewriteCond %{SCRIPT_FILENAME} ^load-styles.php$
    RewriteCond %{REQUEST_URI} ^.*?/?wp-admin/?.*$
    RewriteRule ^.* - [F,L]
    
    RewriteCond %{REQUEST_URI} ^.*?/' . preg_quote($login_url) . '\??.*$
    RewriteRule ^(.*?/?)' . preg_quote($login_url) . '(\??.*)$ $1wp-login.php$2 [L,E=IS_LOGIN:1]
    
    RewriteCond %{ENV:REDIRECT_IS_LOGIN} !^1$
    RewriteCond %{SCRIPT_FILENAME} ^.*?/wp-login.php$
    RewriteRule ^.* - [F,L]
    # END sumedia-urlify';
    }
}