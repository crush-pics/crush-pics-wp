<?php

if (!defined('ABSPATH'))
    exit;

class Image_Compression_Ajax {

    protected static $crush_all_process;

    public function __construct() {
        //validate account
        add_action('wp_ajax_wpic_validate_account', array($this, 'wpic_validate_account'));
        //create account
        add_action('wp_ajax_wpic_create_account', array($this, 'wpic_create_account'));
        //save custom quality
        add_action('wp_ajax_wpic_custom_quality_data_save', array($this, 'wpic_custom_quality_data_save'));
        //image compress
        add_action('wp_ajax_wpic_image_compress', array($this, 'wpic_image_compress'));
        //image compress
        add_action('wp_ajax_wpic_single_image_media_compress', array($this, 'wpic_single_image_media_compress'));
        //image restore
        add_action('wp_ajax_wpic_image_restore', array($this, 'wpic_image_restore'));
        //image check compress
        add_action('wp_ajax_wpic_image_check_compress', array($this, 'wpic_image_check_compress'));
        //fill image_details
        add_action('wp_ajax_wpic_fill_image_details', array($this, 'wpic_fill_image_details'));
        //image check status
        add_action('wp_ajax_wpic_image_check_status', array($this, 'wpic_image_check_status'));
        //change compression auto
        add_action('wp_ajax_wpic_change_compression_auto', array($this, 'wpic_change_compression_auto'));
        //update quota used card
        add_action('wp_ajax_wpic_update_quota_used_card', array($this, 'wpic_update_quota_used_card'));
        // image compress all
        add_action('wp_ajax_wpic_image_check_crush_all', array($this, 'wpic_image_check_crush_all'));
        // save image compressiob type
        add_action('wp_ajax_wpic_compression_type_data_save', array($this, 'wpic_compression_type_data_save'));
        // stop compress all
        add_action('wp_ajax_wpic_image_stop_compress_all', array($this, 'wpic_image_stop_compress_all'));
        //reload image table
        add_action('wp_ajax_wpic_reload_image_table', array($this, 'wpic_reload_image_table'));
    }

    /*
     * Dashboard update quota used card
     */

    public function wpic_update_quota_used_card() {
        $data = array();
        $plan_data = get_option('wpic_plan_data');
        if ($plan_data) {
            $quota_usage = round($plan_data['quota_usage'] / 1000000, 2);
            $bytes = round($plan_data['bytes'] / 1000000, 2);
            $quota_usage_precentage = ($quota_usage / $bytes) * 100;
            if ($quota_usage >= $bytes) {
                $crush_webapp_url = Image_Compression_Menu::CRUSH_WEBAPP_URL;
                $data = array('status' => 'upgrade', 'upgrade_url' => $crush_webapp_url, 'quota_usage' => $quota_usage, 'bytes' => $bytes, 'precentage' => $quota_usage_precentage);
            } else {
                $data = array('status' => 'valid', 'quota_usage' => $quota_usage, 'bytes' => $bytes, 'precentage' => $quota_usage_precentage);
            }
        }

        wp_send_json($data);
        wp_die();
    }

    public function wpic_change_compression_auto() {
        $value = sanitize_text_field($_POST['value']);
        update_option('compression_auto', $value);
        wp_die();
    }

    public function wpic_image_compress() {
        $id = absint($_POST['id']);
        $image_url = esc_url($_POST['url']);
        $size = sanitize_text_field($_POST['size']);
        $compression_type = get_option('compression_type', 'balanced');
        $response = Image_Functions::compress_image($id, $image_url, $size, $compression_type);
        wp_send_json($response);
        wp_die();
    }

    public function wpic_single_image_media_compress() {
        $id = absint($_POST['id']);
        $image_url = esc_url($_POST['url']);
        $size = sanitize_text_field($_POST['size']);
        if (!empty($id)) {
            $compression_type = get_post_meta($id, 'image_compression_type', true);
        }
        if (empty($compression_type)) {
            $compression_type = get_option('compression_type', '-');
        }

        $response = Image_Functions::compress_image($id, $image_url, $size, $compression_type);
        wp_send_json($response);
        wp_die();
    }

    public function wpic_image_restore() {

        $id = absint($_POST['id']);
        $image_url = esc_url($_POST['url']);
        $size = sanitize_text_field($_POST['size']);
        $backup_image = esc_url($_POST['backup_image']);
        $response = Image_Functions::restore_image($id, $image_url, $size, $backup_image);
        wp_send_json($response);
        wp_die();
    }

    public function wpic_image_check_compress() {
        $crushed_id = absint($_POST['id']);
        $response = Image_Functions::check_image_compress($crushed_id);
        wp_send_json($response);
        wp_die();
    }

    function wpic_fill_image_details() {
        $id = absint($_POST['id']);
        $type = sanitize_text_field($_POST['type']);
        $size = sanitize_text_field($_POST['size']);
        $response = Image_Functions::fill_image_details($id, $type, $size);
        wp_send_json($response);
        wp_die();
    }

    function wpic_image_check_status() {
        $id = absint($_POST['id']);
        $size = sanitize_text_field($_POST['size']);
        $response = Image_Functions::check_image_status($id, $size);
        wp_send_json($response);
        wp_die();
    }

    public function wpic_validate_account() {
        $api_key = str_replace(array("\r\n", "\r", "\n"), '', trim($_POST['api_key']));

        $results = Api_Requests::api_key_validation_request($api_key);
        $results = json_decode($results, true);

        if (isset($results['shop']['plan_data']) && isset($results['shop']['login'])) {
            $quota_usage = 0;
            $bytes = 0;
            if (!empty($results['shop']['plan_data']['quota_usage']))
                $quota_usage = round($results['shop']['plan_data']['quota_usage'] / 1000000, 2);

            if (!empty($results['shop']['plan_data']['bytes']))
                $bytes = round($results['shop']['plan_data']['bytes'] / 1000000, 2);

            if ($quota_usage >= $bytes) {
                update_option('quota_end', 'yes', 'no');
            } else {
                delete_option('quota_end');
            }
            //save options
            update_option('wpic_api_key', $api_key);
            update_option('wpic_shop_identifier', $results['shop']['login']);
            update_option('wpic_plan_data', $results['shop']['plan_data']);
            update_option('wpic_plan_next_charge', $results['shop']['next_charge_at']);

            //update callback url
            Api_Requests::add_callback_url($api_key, get_rest_url(null, 'crush/webhook'));

            echo 'valid';
        } else {
            echo 'not-valid';
        }

        wp_die();
    }

    public function wpic_create_account() {
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        $results = Api_Requests::registration_request($email, $password, $password);
        $results = json_decode($results, true);

        if (isset($results['id'])) {
            echo 'done';
        } elseif (isset($results['errors']['email']['0'])) {
            echo 'exist';
        } else {
            echo 'error';
        }

        wp_die();
    }

    public function wpic_custom_quality_data_save() {
        $jpeg_quality = absint($_POST['jpeg_quality']);
        $png_quality = absint($_POST['png_quality']);
        $gif_quality = absint($_POST['gif_quality']);

        update_option('compression_type_custom_jpeg', $jpeg_quality);
        update_option('compression_type_custom_png', $png_quality);
        update_option('compression_type_custom_gif', $gif_quality);

        echo 'done';

        wp_die();
    }

    function wpic_image_check_crush_all() {
        $response = Image_Functions::image_check_compress_all();
        wp_send_json($response);
        wp_die();
    }

    function wpic_compression_type_data_save() {
        if (!empty($_POST['id'])) {
            $post_id = absint($_POST['id']);
            update_post_meta($post_id, 'image_compression_type', sanitize_text_field($_POST['compression_type']));
        }
        $response = Image_Functions::get_compression_type_text(sanitize_text_field($_POST['compression_type']));
        echo $response;
        wp_die();
    }

    function wpic_image_stop_compress_all() {
        self::$crush_all_process = new WP_Crush_All_Process();
        self::$crush_all_process->cancel_process();
        global $wpdb;
        $table_crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
        $wpdb->query("TRUNCATE TABLE $table_crush_image_all_sizes");
        delete_option('crush_total');
        delete_option('crush_loop_no');
        delete_option('crush_current_loop');
        delete_option('crush_start');
        delete_option('crush_status');
        delete_option('crush_end');
        $response = admin_url() . 'admin.php?page=crush-pics';
        echo $response;
        wp_die();
    }

    function wpic_reload_image_table() {
        ob_start();
        global $wpdb;
        //check if api activated
        $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
        $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
        //total images count and crushed images count
        $enabled_sizes = get_option('compression_sizes');
        if (!$enabled_sizes)
            $enabled_sizes = array('full');
        else
            $enabled_sizes[] = 'full';

        $images_count = $wpdb->get_row('select count(id) as images_count from ' . $table_image_sizes . ' where image_size in ("' . implode('", "', $enabled_sizes) . '")');
        if ($images_count)
            $images_count = $images_count->images_count;


        $crushed_images_count = $wpdb->get_row('select count(id) as crushed_images_count from ' . $crush_image_actions_table . ' where action IN ("crushed","error") and is_history = 0 and image_size in ("' . implode('", "', $enabled_sizes) . '")');
        if ($crushed_images_count)
            $crushed_images_count = $crushed_images_count->crushed_images_count;


        //get latest images
        $paged = 1;
        $image_term = '';
        $limit = 10;
        $offset = ( $paged - 1 ) * $limit;
        $total_images = Image_Functions::list_all_full_image($image_term);
        $total = count($total_images);
        $num_of_pages = ceil($total / $limit);
        $images = Image_Functions::list_all_full_image($image_term, $offset, $limit);
        //get user plan data
        $plan_data = get_option('wpic_plan_data');
        $crush_next_charge = get_option('wpic_plan_next_charge');
        $next_charge_days = '-';
        if ($crush_next_charge) {
            $today = new DateTime();
            $today->setTimestamp(current_time('timestamp'));
            $crush_next_charge = new DateTime($crush_next_charge);
            $next_charge_days = $crush_next_charge->diff($today)->days;

            $next_charge_days = sprintf(_n('%s Day', '%s Days', $next_charge_days, 'wp-image-compression'), $next_charge_days);
        }
        $quota_usage = round($plan_data['quota_usage'] / 1000000, 2);
        $bytes = round($plan_data['bytes'] / 1000000, 2);
        $quota_usage_precentage = '';
        if (!empty($bytes)) {
            $quota_usage_precentage = ($quota_usage / $bytes) * 100;
        }
        $compression_auto = get_option('compression_auto');
        $compression_type = get_option('compression_type', '-');
        switch ($compression_type) {
            case 'balanced':
                $compression_type = 'Balanced';
                break;
            case 'lossless':
                $compression_type = 'Conservative';
                break;
            case 'lossy':
                $compression_type = 'Custom';
                break;
            default:
                break;
        }


        //dashboard
        include WPIC_PATH . 'inc/views/image-list-template.php';
        echo ob_get_clean();
        wp_die();
    }

}
