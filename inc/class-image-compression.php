<?php

if (!defined('ABSPATH'))
    exit;

class Image_Compression {

    public function __construct() {
        //create tables
        register_activation_hook(WPIC_FILE, array($this, 'on_activate'));
        // Creating table whenever a new blog is created
        add_action( 'wpmu_new_blog', 'on_create_blog', 10, 6 );
        // Deleting the table whenever a blog is deleted
        add_filter( 'wpmu_drop_tables', 'on_delete_blog' );
    }

    // Creating tables for all blogs in a WordPress Multisite installation
    function on_activate( $network_wide ) {
        global $wpdb;
        if ( is_multisite() && $network_wide ) {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                $this->wp_crush_activation();
                restore_current_blog();
            }
        } else {
            $this->wp_crush_activation();
        }
        

        //check if api activated
        $api_key = get_option( 'wpic_api_key' );
        if ( ! $api_key ) {
            set_transient( '_welcome_screen_activation_redirect', true, 30 );
        }
    }

    public static function wp_crush_activation() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $charset_collate = $wpdb->get_charset_collate();

        //image actions table
        $table_image_actions = $wpdb->prefix . 'crush_image_actions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_image_actions'") != $table_image_actions) {
            $sql = "CREATE TABLE " . $table_image_actions . " (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `crushed_id` bigint(20) NOT NULL,
			`image_id` bigint(20) NOT NULL,
                        `image_size` varchar(255) NOT NULL, 
                        `image_path` varchar(255) NOT NULL,                                               
                        `action` varchar(255) NOT NULL,
                        `created_at` datetime NOT NULL, 
                        `saved` float NULL,
                        `is_history` BOOLEAN NOT NULL DEFAULT 0,
                        `original_size` bigint(32) NOT NULL,
                        `crushed_size` bigint(32) NOT NULL,                                               
                        `compression_type` varchar(255) NOT NULL,
                        `image_backup_path` varchar(255) NULL DEFAULT '',
                        `exif_data` text NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) $charset_collate;";
            dbDelta($sql);
        }
        //image sizes table
        $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_image_sizes'") != $table_image_sizes) {
            $sql = "CREATE TABLE " . $table_image_sizes . " (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
			`image_id` bigint(20) NOT NULL,
                        `image_size` varchar(255) NOT NULL,
                        `image_size_path` varchar(255) NULL DEFAULT '',
                        `image_file_size` varchar(255) NULL DEFAULT '',                        
			PRIMARY KEY (`id`)
		) $charset_collate;";
            dbDelta($sql);
        }
        //image crush all sizes table
        $table_crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_crush_image_all_sizes'") != $table_crush_image_all_sizes) {
            $sql = "CREATE TABLE " . $table_crush_image_all_sizes . " (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
			`image_id` bigint(20) NOT NULL,
                        `crushed_id` bigint(20) NOT NULL,
                        `image_size` varchar(255) NOT NULL,
                        `image_url` varchar(255) NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) $charset_collate;";
            dbDelta($sql);
        }
        
        //cache/remove images
        Image_Functions::site_image_sizes_handling();
    }

    // Creating tables whenever a new blog is created
    function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        if ( is_plugin_active_for_network( 'plugin-name/plugin-name.php' ) ) {
            switch_to_blog( $blog_id );
            $this->wp_crush_activation();
            restore_current_blog();
        }
    }

    // Deleting tables whenever a blog is deleted
    function on_delete_blog( $tables ) {
        global $wpdb;
        $tables[] = $wpdb->prefix . 'table_name';
        return $tables;
    }

}
