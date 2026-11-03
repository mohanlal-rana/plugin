<?php

if (!defined('ABSPATH')) {
    exit;
}

class Astro_Space_Database
{
    public static function create_tables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'astro_space_images';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            image_id bigint(20) unsigned NOT NULL,
            title varchar(255) NOT NULL,
            description text NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY image_id (image_id),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}