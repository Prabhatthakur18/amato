<?php
if (!defined('ABSPATH')) exit;

class WSSC_DB {
    public static function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'wssc_bulk_requests';
        $charset = $wpdb->get_charset_collate();
        
        // Check if table exists first
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id TEXT NOT NULL,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(255) NOT NULL,
                quantity INT(11) NOT NULL,
                message TEXT,
                status VARCHAR(20) DEFAULT 'pending',
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id)
            ) $charset;";
            
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
        
        // Add missing columns if table exists but columns are missing
        $columns = $wpdb->get_col("DESCRIBE $table", 0);
        
        if (!in_array('email', $columns)) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN email VARCHAR(255) NOT NULL AFTER phone");
        }
    }
}