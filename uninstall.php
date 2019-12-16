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
