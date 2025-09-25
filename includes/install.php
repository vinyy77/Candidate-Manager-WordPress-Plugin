<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}


// inside candidate-manager.php (activation hook)
function candidate_manager_install(): void {
    global $wpdb;

    $table_name      = $wpdb->prefix . 'candidates';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        other_names varchar(100) DEFAULT '' NOT NULL,
        age int(3) DEFAULT 0 NOT NULL,
        address1 varchar(255) DEFAULT '' NOT NULL,
        address2 varchar(255) DEFAULT '' NOT NULL,
        city varchar(100) DEFAULT '' NOT NULL,
        state varchar(100) DEFAULT '' NOT NULL,
        zip varchar(20) DEFAULT '' NOT NULL,
        phone varchar(50) DEFAULT '' NOT NULL,
        email varchar(100) DEFAULT '' NOT NULL,
        position varchar(100) DEFAULT '' NOT NULL,
        license_file varchar(255) DEFAULT '' NOT NULL,
        resume_file varchar(255) DEFAULT '' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'candidate_manager_install');
