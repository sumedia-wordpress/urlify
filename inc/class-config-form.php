<?php

class Sumedia_Urlify_Config_Form
{
    public $table_name = 'sumedia_urlify_urls';

    /**
     * @var array
     */
    protected $data = [];

    public function __construct()
    {
        $this->data = [
            'admin_url' => 'wp-admin',
            'login_url' => 'wp-login.php'
        ];
    }

    public function load()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $results = $wpdb->get_results("SELECT * FROM `" . $table_name . "`", ARRAY_A);
        foreach ($results as $row) {
            switch($row['urltype']) {
                case 'admin_url':
                    $this->data['admin_url'] = $row['url'];
                    break;
                case 'login_url':
                    $this->data['login_url'] = $row['url'];
                    break;
            }
        }
    }

    public function get_data()
    {
        return $this->data;
    }

    public function do_request($request_data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        if (isset($request_data['admin_url']) && preg_match('#^[a-z0-9._/-]+$#i', $request_data['admin_url'])) {
            $this->data['admin_url'] = $request_data['admin_url'];
        }

        if (isset($request_data['login_url']) && preg_match('#^[a-z0-9._/-]+$#i', $request_data['login_url'])) {
            $this->data['login_url'] = $request_data['login_url'];
        }
    }

    public function save()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $sql = "INSERT IGNORE INTO `" . $table_name . "` (urltype, url) VALUES ('admin_url', %s)";
        $prepare = $wpdb->prepare($sql, $this->data['admin_url']);
        $wpdb->query($prepare);

        $sql = "UPDATE `" . $table_name . "` SET `url` = %s WHERE `urltype` = 'admin_url'";
        $prepare = $wpdb->prepare($sql, $this->data['admin_url']);
        $wpdb->query($prepare);

        $sql = "INSERT IGNORE INTO `" . $table_name . "` (urltype, url) VALUES ('login_url', %s)";
        $prepare = $wpdb->prepare($sql, $this->data['login_url']);
        $wpdb->query($prepare);

        $sql = "UPDATE `" . $table_name . "` SET `url` = %s WHERE `urltype` = 'login_url'";
        $prepare = $wpdb->prepare($sql, $this->data['login_url']);
        $wpdb->query($prepare);

        $wp_config = new Sumedia_Urlify_Configure_WPConfig();
        $wp_config->write($this->data['admin_url']);

        $ht_config = new Sumedia_Urlify_Configure_Htaccess();
        $ht_config->write($this->data['admin_url'], $this->data['login_url']);
    }
}