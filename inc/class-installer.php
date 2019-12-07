<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

class Sumedia_Urlify_Installer
{
    /**
     * @var string
     */
    protected $installedVersion;

    /**
     * @var string
     */
    protected $currentVersion;

    /**
     * @var string
     */
    protected $optionName = 'sumedia_urlify_version';

    /**
     * @var wpdb
     */
    protected $db;

    public function __construct()
    {
        global $wpdb;
        $this->installedVersion = get_option('sumedia_urlify_version');
        $this->currentVersion = SUMEDIA_URLIFY_VERSION;
        $this->db = $wpdb;
    }

    public function install()
    {
        if (-1 == version_compare($this->installedVersion, $this->currentVersion)) {
            if (-1 == version_compare($this->installedVersion, '0.1.0')) {
                $this->install_urlify_table();
            }
            add_option($this->optionName, $this->currentVersion);
        }
    }

    protected function install_urlify_table()
    {
        $charset_collate = $this->db->get_charset_collate();
        $table_name = $this->db->prefix . 'sumedia_urlify_urls';

        $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `urltype` VARCHAR(64) NOT NULL UNIQUE KEY,
            `url` VARCHAR(128) NOT NULL UNIQUE KEY            
        ) $charset_collate;";
        dbDelta($sql);
    }
}