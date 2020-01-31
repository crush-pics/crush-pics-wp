<?php

/**
 * Plugin Name: Crush.pics Image Optimizer – Image Compression and Optimization
 * Plugin URI: https://crush.pics/platforms/wordpress-plugin
 * Description: Image Compression and Optimization using Crush.pics API
 * Version: 1.8.3
 * Author: Space Squirrel Ltd.
 * Author URI: https://crush.pics
 * Text Domain: crush-pics-image-compression-optimization
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 5.4
 */
if (!defined('ABSPATH'))
    exit;

define('WPIC_URL', plugin_dir_url(__FILE__));
define('WPIC_PATH', plugin_dir_path(__FILE__));
define('WPIC_FILE', __FILE__);
define('WPIC_VERSION', '1.8.3');

//main class
require_once WPIC_PATH . 'inc/class-image-compression.php';
new Image_Compression();

//api requests
require_once WPIC_PATH . 'inc/class-api-requests.php';
new Api_Requests();

//menu items
require_once WPIC_PATH . 'inc/class-admin-menu.php';
new Image_Compression_Menu();

//ajax class
require_once WPIC_PATH . 'inc/class-ajax.php';
new Image_Compression_Ajax();

//image process
require_once WPIC_PATH . 'inc/class-image-process.php';
new Image_Process();

//image function
require_once WPIC_PATH . 'inc/class-image-functions.php';
new Image_Functions();

//media list / view
require_once WPIC_PATH . 'inc/class-media-list-view.php';
new Media_List_View();

require_once WPIC_PATH . 'inc/class-update-progress.php';
new Update_Progress();

require_once WPIC_PATH . 'inc/class-crush-all-process.php';
new WP_Crush_All_Process();
