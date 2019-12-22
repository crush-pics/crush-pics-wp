<?php

if (!defined('ABSPATH'))
    exit;

class Api_Requests {

//    const REGISTER_URL = 'https://app.crush.pics/users';
//    const API_URL = 'https://api.crush.pics/v1/';

    const REGISTER_URL = 'https://appstaging.crush.pics/users'; //stage
    const API_URL = 'https://apistaging.crush.pics/v1/'; //stage

    public function __construct() {
        //create weebhook listener
        add_action('rest_api_init', function () {
            register_rest_route('crush', '/webhook', array(
                'methods' => 'POST',
                'callback' => array($this, 'wpic_webhooks_listener'),
            ));
        });
        //update callback url when permalink changed
        add_action('update_option_permalink_structure', array($this, 'update_crush_callback_url'));
    }

    public function update_crush_callback_url() {
        //update callback url
        $api_key = get_option('wpic_api_key');
        Api_Requests::add_callback_url($api_key, get_rest_url(null, 'crush/webhook'));
    }

    /**
     * update plan data
     * @param type $request
     */
    public function wpic_webhooks_listener($request) {
        $parameters = $request->get_json_params();

        $shop_identifier = get_option('wpic_shop_identifier');
        if (isset($parameters['event']) && isset($parameters['shop_identifier']) && $parameters['shop_identifier'] == $shop_identifier) {
            if ($parameters['event'] == 'optimized') {
                $plan_data = get_option('wpic_plan_data');
                $plan_data['quota_usage'] = $parameters['quota_usage'];
                update_option('wpic_plan_data', $plan_data);
                // update compress image
                Image_Functions::compress_image_webhook_callaback($parameters);
                if (get_option('crush_start') == 'yes') {
                    // update compress all image table
                    Image_Functions::compress_all_image_webhook_callaback($parameters);
                }
            } elseif ($parameters['event'] == 'error') {
                // update compress image
                Image_Functions::compress_image_webhook_callaback($parameters, 'error');
                if (get_option('crush_start') == 'yes') {
                    // update compress all image table
                    Image_Functions::compress_all_image_webhook_callaback($parameters);
                }
            } elseif ($parameters['event'] == 'subscription_updated') {
                update_option('wpic_plan_data', $parameters['plan_data']);
                update_option('wpic_plan_next_charge', $parameters['next_charge_at']);
            }

            //check for quota status
            $plan_data = get_option('wpic_plan_data');
            $quota_usage = $plan_data['quota_usage'];
            $bytes = $plan_data['bytes'];

            if ($quota_usage >= $bytes) {
                update_option('quota_end', 'yes', 'no');
            } else {
                delete_option('quota_end');
            }
        }

        // Create the response object
        $response = new WP_REST_Response($request);
        // Add a custom status code
        $response->set_status(201);
        return $response;
    }

    public static function add_callback_url($api_key, $callback_url) {
        $body = '';
        if ($api_key && $callback_url) {

            $url = self::API_URL . 'callback_urls';

            $data = array('url' => $callback_url);

            $args = array(
                'body' => json_encode($data),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                )
            );

            $response = wp_remote_post($url, $args);
            if (is_array($response) && !is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
            }
        }
        return $body;
    }

    /**
     * create user account request for free plan
     * @param type $email
     * @param type $password_confirmation
     * @param type $password
     * @return type
     */
    public static function registration_request($email, $password_confirmation, $password) {
        $body = '';
        if (!empty($email) && !empty($password_confirmation) && !empty($password)) {
            $url = self::REGISTER_URL;

            $data = array(
                'user' => array(
                    'email' => $email,
                    'password_confirmation' => $password_confirmation,
                    'password' => $password,
            ));

            $args = array(
                'body' => json_encode($data),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                )
            );

            $response = wp_remote_post($url, $args);
            if (is_array($response) && !is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
            }
        }
        return $body;
    }

    /**
     *  get user details by valid api key
     * @param type $api_key
     * @return type
     */
    public static function api_key_validation_request($api_key) {
        $body = '';
        if (!empty($api_key)) {

            $url = self::API_URL . 'shop';
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                )
            );

            $response = wp_remote_get($url, $args);
            if (is_array($response) && !is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
            }
        }
        return $body;
    }

    /**
     * image compress request data
     * @param type $api_key
     * @param type $image_url
     * @param type $compression_type
     * @param type $compression_level
     * @return type
     */
    public static function compress_request($api_key, $image_url, $compression_type, $compression_level = null) {
        $body = array('status' => '');
        if (!empty($api_key) && !empty($image_url) && !empty($compression_type)) {

            $url = self::API_URL . 'original_images';

            $data = array(
                'image_url' => $image_url,
                'compression_type' => $compression_type,
                'origin' => 'wp',
            );

            if (!empty($compression_level) && $compression_type == 'lossy') {
                $data['compression_level'] = $compression_level;
            }

            $args = array(
                'body' => json_encode($data),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                )
            );

            $response = wp_remote_post($url, $args);
            if (is_array($response) && !is_wp_error($response)) {
                $body['result'] = wp_remote_retrieve_body($response);
                $body['status'] = wp_remote_retrieve_response_code($response);
            }
        }
        return $body;
    }

}
