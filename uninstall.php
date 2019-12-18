<?php

/**
 * WP image compression Uninstall
 *
 */
defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('wpic_api_key');
delete_option('wpic_plan_data');
delete_option('wpic_shop_identifier');
delete_option('wpic_plan_next_charge');
delete_option('compression_type');


global $wpdb;


// Drop plugin table s on uninstall
$crush_image_actions = $wpdb->prefix . 'crush_image_actions';
$crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
$crush_image_sizes = $wpdb->prefix . 'crush_image_sizes';

$wpdb->query("DROP TABLE {$crush_image_actions}");
$wpdb->query("DROP TABLE {$crush_image_all_sizes}");
$wpdb->query("DROP TABLE {$crush_image_sizes}");

// delete backups
$upload_dir = wp_upload_dir();
$backup_dir = $upload_dir['basedir'] . '/crushed-backup/';

$it = new RecursiveDirectoryIterator($backup_dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);
foreach($files as $file) {
    if ($file->isDir()){
        rmdir($file->getRealPath());
    } else {
        unlink($file->getRealPath());
    }
}
rmdir($backup_dir);
               