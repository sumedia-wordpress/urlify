<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

class Sumedia_Urlify_Installer
{
    /**
     * @var string
     */
    protected $currentVersion;

    /**
     * @var string
     */
    protected $optionName = 'sumedia_urlify_version';

    /**
     * @var string
     */
    protected $table_name = 'sumedia_urlify_urls';

    public function __construct()
    {
        $this->currentVersion = SUMEDIA_URLIFY_VERSION;
    }

    public function install()
    {
        $this->install_urlify_table();
        add_option($this->optionName, $this->currentVersion);
    }

    protected function install_urlify_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->table_name;

        $query = "SHOW TABLES LIKE '" . $table_name . "'";
        $row = $wpdb->get_row($query);
        if ($row) {
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE `$table_name` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `urltype` VARCHAR(64) NOT NULL UNIQUE KEY,
            `url` VARCHAR(128) NOT NULL UNIQUE KEY            
        ) $charset_collate;";
        dbDelta($sql);
    }
}