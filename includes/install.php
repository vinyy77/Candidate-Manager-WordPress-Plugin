<?php
function candidate_manager_install() {
    global $wpdb;
    $table = $wpdb->prefix . "candidates";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        full_name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(50),
        age int(3),
        address1 varchar(255),
        address2 varchar(255),
        city varchar(100),
        state varchar(100),
        zip varchar(20),
        position varchar(255),
        license_file varchar(255),
        resume_file varchar(255),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
