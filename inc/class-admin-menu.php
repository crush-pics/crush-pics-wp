<?php

if (!defined('ABSPATH'))
    exit;

class Image_Compression_Menu {

    const CRUSH_WEBAPP_URL = 'https://app.crush.pics';

    public function __construct() {
        //menu items
        add_action('admin_menu', array($this, 'register_plugin_menu'));
        //enqueue script
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_script'));
        //call register settings function
        add_action('admin_init', array($this, 'wpic_register_plugin_settings'));
        //settings page redirect
        add_filter('wp_redirect', array($this, 'wpic_settings_redirect'));
        // handle crush all background process
        add_action('init', array($this, 'crush_all_process_handler'));
    }

    public function wpic_settings_redirect($location) {
        if (isset($_POST['api_key']) && $_POST['api_key'] == 'true') {
            $location = '?page=crush-pics';
        }
        return $location;
    }

    public function register_plugin_menu() {
        //check if settings saved
        $compression_type = get_option('compression_type');

        if ($compression_type) {
            add_menu_page('Crush Pics', __('Crush.pics', 'wp-image-compression'), 'manage_options', 'crush-pics', array($this, 'register_main_plugin_menu_page'), WPIC_URL . 'assets/img/crush-icon.svg');

            //check if api activated
            $api_key = get_option('wpic_api_key');
            if ($api_key) {
                add_submenu_page('crush-pics', 'Crush Pics', 'Dashboard', 'manage_options', 'crush-pics');
                add_submenu_page('crush-pics', 'Crush Pics Settings', 'Settings', 'manage_options', 'crush-pics-settings', array($this, 'register_plugin_settings_menu_page'));
            }
        } else {
            //check if api activated
            $api_key = get_option('wpic_api_key');
            if ($api_key) {
                add_menu_page('Crush Pics', __('Crush.pics', 'wp-image-compression'), 'manage_options', 'crush-pics-settings', array($this, 'register_plugin_settings_menu_page'), WPIC_URL . 'assets/img/crush-icon.svg');
            } else {
                add_menu_page('Crush Pics', __('Crush.pics', 'wp-image-compression'), 'manage_options', 'crush-pics', array($this, 'register_main_plugin_menu_page'), WPIC_URL . 'assets/img/crush-icon.svg');
            }
        }
    }

    public function register_main_plugin_menu_page() {
        global $wpdb;
        //check if api activated
        $api_key = get_option('wpic_api_key');
        $crush_webapp_url = self::CRUSH_WEBAPP_URL;
        if ($api_key) {

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

            $media_count = $wpdb->get_row("select count(ID) as media_count  from $wpdb->posts p where p.post_type = 'attachment' and p.post_status = 'inherit' and (p.post_mime_type = 'image/jpeg' OR p.post_mime_type = 'image/gif' OR p.post_mime_type = 'image/png')");
            if ($media_count)
                $media_count = $media_count->media_count;
            //get latest images
            $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
            $image_term = isset($_GET['image_term']) ? sanitize_text_field($_GET['image_term']) : '';
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
            $quota_usage = 0;
            $bytes = 0;
            $plan_name = '';
            if (!empty($plan_data['quota_usage']))
                $quota_usage = round($plan_data['quota_usage'] / 1000000, 2);

            if (!empty($plan_data['bytes']))
                $bytes = round($plan_data['bytes'] / 1000000, 2);

            if (!empty($plan_data['name']))
                $plan_name = $plan_data['name'];

            $quota_usage_precentage = '';
            if (!empty($bytes)) {
                $quota_usage_precentage = ($quota_usage / $bytes) * 100;
            }
            $upgrade_plan = 'no';
            if ($quota_usage >= $bytes) {
                $upgrade_plan = 'yes';
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


            //check if crush all in-progress
            $crush_all = get_option('crush_start');

            //dashboard
            require_once WPIC_PATH . 'inc/views/dashboard.php';
        } else {
            $is_register_done = isset($_GET['register']) ? true : false;
            $is_email_exist = isset($_GET['email']) ? sanitize_email($_GET['email']) : false;

            //add api key
            require_once WPIC_PATH . 'inc/views/validate-create-account.php';
        }
    }

    public function wpic_register_plugin_settings() {
        register_setting('wpic-plugin-settings-group', 'compression_type');
        register_setting('wpic-plugin-settings-group', 'compression_auto');
        register_setting('wpic-plugin-settings-group', 'compression_backup');
        register_setting('wpic-plugin-settings-group', 'compression_exif');
        register_setting('wpic-plugin-settings-group', 'compression_sizes');
        register_setting('wpic-plugin-settings-group', 'compression_type_custom_jpeg');
        register_setting('wpic-plugin-settings-group', 'compression_type_custom_png');
        register_setting('wpic-plugin-settings-group', 'compression_type_custom_gif');
    }

    public function register_plugin_settings_menu_page() {
        $image_sizes = $this->get_image_sizes();

        $compression_type = get_option('compression_type');
        if (!$compression_type)
            $compression_type = 'balanced';

        $compression_auto = get_option('compression_auto');
        $compression_backup = get_option('compression_backup');
        $compression_exif = get_option('compression_exif');
        $compression_sizes = get_option('compression_sizes');
        if (!$compression_sizes)
            $compression_sizes = array();

        $compression_type_custom_jpeg = get_option('compression_type_custom_jpeg');
        $compression_type_custom_png = get_option('compression_type_custom_png');
        $compression_type_custom_gif = get_option('compression_type_custom_gif');

        $compression_custom_size_details = array(
            'jpeg' => $compression_type_custom_jpeg,
            'png' => $compression_type_custom_png,
            'gif' => $compression_type_custom_gif,
        );

        $api_key = get_option('wpic_api_key');
        $api_key_added = isset($_GET['api_key']) && $_GET['api_key'] == 'true' ? true : false;


        require_once WPIC_PATH . 'inc/views/settings.php';
    }

    public function get_image_sizes() {
        global $_wp_additional_image_sizes;

        $sizes = array();

        foreach (get_intermediate_image_sizes() as $_size) {
            if (in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
                $sizes[$_size]['width'] = get_option("{$_size}_size_w");
                $sizes[$_size]['height'] = get_option("{$_size}_size_h");
                $sizes[$_size]['crop'] = (bool) get_option("{$_size}_crop");
            } elseif (isset($_wp_additional_image_sizes[$_size])) {
                $sizes[$_size] = array(
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                );
            }
        }

        return $sizes;
    }

    public function enqueue_admin_script($hook) {
        if ($hook == 'toplevel_page_crush-pics' || $hook == 'crush-pics_page_crush-pics-settings' || $hook == 'toplevel_page_crush-pics-settings') {
            wp_enqueue_script('wpic_popper_script', WPIC_URL . 'assets/js/popper.min.js', array('jquery'), WPIC_VERSION, true);
            wp_enqueue_script('wpic_bootstrap_script', WPIC_URL . 'assets/js/bootstrap.min.js', array('jquery'), WPIC_VERSION, true);

            wp_enqueue_style('wpic_jqui_style', WPIC_URL . 'assets/css/jquery-ui.min.css', array(), WPIC_VERSION);
            wp_enqueue_style('wpic_borealis_style', WPIC_URL . 'assets/css/borealis-theme.min.css', array(), WPIC_VERSION);
            wp_enqueue_style('wpic_style', WPIC_URL . 'assets/css/styles.css', array(), WPIC_VERSION);

            $compression_type_custom_jpeg = get_option('compression_type_custom_jpeg');
            $compression_type_custom_png = get_option('compression_type_custom_png');
            $compression_type_custom_gif = get_option('compression_type_custom_gif');



            wp_enqueue_script('wpic_script', WPIC_URL . 'assets/js/scripts.js', array('jquery','jquery-ui-slider'), WPIC_VERSION, true);
            wp_localize_script('wpic_script', 'wpic', array(
                'api_key_validate' => __('Checking API Key', 'wp-image-compression'),
                'api_key_validate_accepted' => __('API Key Accepted', 'wp-image-compression'),
                'api_key_validate_error' => __('Wrong API Key', 'wp-image-compression'),
                'api_key_valid_message' => __('API Key is Verified Successfully', 'wp-image-compression'),
                'api_key_creat_required_field_error' => __('Required field.', 'wp-image-compression'),
                'api_key_creat_email_field_error' => __('Email not valid.', 'wp-image-compression'),
                'api_key_creat_password_field_error' => __('Required field (minimum is 6 characters).', 'wp-image-compression'),
                'api_key_creat_password_confirm_field_error' => __('Password and confirm password not match.', 'wp-image-compression'),
                'api_key_create' => __('Generating API Key', 'wp-image-compression'),
                'api_key_create_email_exist' => __('Email has already been taken.', 'wp-image-compression'),
                'api_key_create_general_error' => __('Something went wrong please try again.', 'wp-image-compression'),
                'api_key_create_account_success' => __('Account created successfully check your mail.', 'wp-image-compression'),
                'custom_quality_data_save' => __('Loading...', 'wp-image-compression'),
                'custom_quality_jpeg' => $compression_type_custom_jpeg ? $compression_type_custom_jpeg : 0,
                'custom_quality_png' => $compression_type_custom_png ? $compression_type_custom_png : 0,
                'custom_quality_gif' => $compression_type_custom_gif ? $compression_type_custom_gif : 0,
            ));
        } elseif ($hook == 'upload.php') {
            $compression_type_custom_jpeg = get_option('compression_type_custom_jpeg');
            $compression_type_custom_png = get_option('compression_type_custom_png');
            $compression_type_custom_gif = get_option('compression_type_custom_gif');
            wp_enqueue_style('wpic_upload_style', WPIC_URL . 'assets/css/upload_styles.css', array(), null);
            wp_enqueue_script('wpic_bootstrap_script', WPIC_URL . 'assets/js/bootstrap.min.js', array('jquery'), null, true);
            wp_enqueue_script('wpic_script', WPIC_URL . 'assets/js/scripts.js', array('jquery'), false, true);
            wp_localize_script('wpic_script', 'wpic', array(
                'api_key_validate' => __('Checking API Key', 'wp-image-compression'),
                'api_key_validate_accepted' => __('API Key Accepted', 'wp-image-compression'),
                'api_key_validate_error' => __('Wrong API Key', 'wp-image-compression'),
                'api_key_valid_message' => __('API Key is Verified Successfully', 'wp-image-compression'),
                'api_key_creat_required_field_error' => __('Required field.', 'wp-image-compression'),
                'api_key_creat_email_field_error' => __('Email not valid.', 'wp-image-compression'),
                'api_key_creat_password_field_error' => __('Required field (minimum is 6 characters).', 'wp-image-compression'),
                'api_key_creat_password_confirm_field_error' => __('Password and confirm password not match.', 'wp-image-compression'),
                'api_key_create' => __('Generating API Key', 'wp-image-compression'),
                'api_key_create_email_exist' => __('Email has already been taken.', 'wp-image-compression'),
                'api_key_create_general_error' => __('Something went wrong please try again.', 'wp-image-compression'),
                'api_key_create_account_success' => __('Account created successfully check your mail.', 'wp-image-compression'),
                'custom_quality_data_save' => __('Loading...', 'wp-image-compression'),
                'custom_quality_jpeg' => $compression_type_custom_jpeg ? $compression_type_custom_jpeg : 0,
                'custom_quality_png' => $compression_type_custom_png ? $compression_type_custom_png : 0,
                'custom_quality_gif' => $compression_type_custom_gif ? $compression_type_custom_gif : 0,
            ));
        } elseif ($hook == 'post.php') {
            global $post;
            if (!empty($post->ID) && wp_attachment_is_image($post->ID)) {
                $compression_type = get_post_meta($post->ID, 'image_compression_type', true);
                if (empty($compression_type)) {
                    $compression_type = get_option('compression_type', '-');
                }
                switch ($compression_type) {
                    case 'balanced':
                        $compression_type = 'compression_balanced_input';
                        break;
                    case 'lossless':
                        $compression_type = 'compression_lossless_input';
                        break;
                    case 'lossy':
                        $compression_type = 'compression_lossy_input';
                        break;
                    default:
                        break;
                }
                wp_enqueue_style('wpic_upload_style', WPIC_URL . 'assets/css/upload_styles.css', array(), null);
                wp_enqueue_script('wpic_bootstrap_script', WPIC_URL . 'assets/js/bootstrap.min.js', array('jquery'), null, true);
                wp_enqueue_script('wpic_single_script', WPIC_URL . 'assets/js/single-scripts.js', array('jquery'), false, true);
                wp_localize_script('wpic_single_script', 'wpic', array(
                    'compression_type_data_save' => __('Loading...', 'wp-image-compression'),
                    'image_id' => $post->ID,
                    'compression_type_id' => $compression_type
                ));
            }
        }
    }

    function crush_all_process_handler() {
        if (!empty($_GET['process']) && $_GET['process'] == 'crush_all') {
            if (empty(get_option('crush_start')) || get_option('crush_start') != 'yes') {
                Image_Functions::handle_crush_all();
            }
        }
    }

}
