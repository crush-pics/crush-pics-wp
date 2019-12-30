<?php

/**
 * WP image compression Uninstall
 *
 */
defined('WP_UNINSTALL_PLUGIN') || exit;


global $wpdb;


// Delete tables for all blogs in a WordPress Multisite
if ( is_multisite() ) {
    // Get all blogs in the network and delete tables on each one
	$offset = 0;
	$limit  = 100;
	while ( $blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs} LIMIT $offset, $limit", ARRAY_A ) ) {
		if ( $blogs ) {
			foreach ( $blogs as $blog ) {
		        switch_to_blog( $blog['blog_id'] );

				delete_option( 'wpic_api_key' );
				delete_option( 'wpic_plan_data' );
				delete_option( 'wpic_shop_identifier' );
				delete_option( 'wpic_plan_next_charge' );
				delete_option( 'compression_type' );
				delete_site_option( 'wpic_api_key' );
				delete_site_option( 'wpic_plan_data' );
				delete_site_option( 'wpic_shop_identifier' );
				delete_site_option( 'wpic_plan_next_charge' );
				delete_site_option( 'compression_type' );

				// Delete plugin tables on uninstall
				$crush_image_actions = $wpdb->prefix . 'crush_image_actions';
				$crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
				$crush_image_sizes = $wpdb->prefix . 'crush_image_sizes';

				$wpdb->query( "DROP TABLE IF EXISTS {$crush_image_actions}" );
				$wpdb->query( "DROP TABLE IF EXISTS {$crush_image_all_sizes}" );
				$wpdb->query( "DROP TABLE IF EXISTS {$crush_image_sizes}" );

				// Delete backups
				$upload_dir = wp_upload_dir();
				$backup_dir = $upload_dir['basedir'] . '/crushed-backup/';

				if( is_dir( $backup_dir ) ) {
					$it = new RecursiveDirectoryIterator( $backup_dir, RecursiveDirectoryIterator::SKIP_DOTS );
					$files = new RecursiveIteratorIterator( $it,
					             RecursiveIteratorIterator::CHILD_FIRST );
					foreach( $files as $file ) {
					    if ( $file->isDir() ){
					        rmdir( $file->getRealPath() );
					    } else {
					        unlink( $file->getRealPath() );
					    }
					}
					rmdir( $backup_dir );
				}
		    }
			restore_current_blog();
		}
		$offset += $limit;
	}
} else {

	delete_option( 'wpic_api_key' );
	delete_option( 'wpic_plan_data' );
	delete_option( 'wpic_shop_identifier' );
	delete_option( 'wpic_plan_next_charge' );
	delete_option( 'compression_type' );

	// Delete plugin tables on uninstall
	$crush_image_actions = $wpdb->prefix . 'crush_image_actions';
	$crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
	$crush_image_sizes = $wpdb->prefix . 'crush_image_sizes';

	$wpdb->query( "DROP TABLE IF EXISTS {$crush_image_actions}" );
	$wpdb->query( "DROP TABLE IF EXISTS {$crush_image_all_sizes}" );
	$wpdb->query( "DROP TABLE IF EXISTS {$crush_image_sizes}" );

	// Delete backups
	$upload_dir = wp_upload_dir();
	$backup_dir = $upload_dir['basedir'] . '/crushed-backup/';

	if( is_dir( $backup_dir ) ) {
		$it = new RecursiveDirectoryIterator( $backup_dir, RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new RecursiveIteratorIterator( $it,
		             RecursiveIteratorIterator::CHILD_FIRST );
		foreach( $files as $file ) {
		    if ( $file->isDir() ){
		        rmdir( $file->getRealPath() );
		    } else {
		        unlink( $file->getRealPath() );
		    }
		}
		rmdir( $backup_dir );
	}
}
