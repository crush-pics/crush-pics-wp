<?php

if (!defined('ABSPATH'))
    exit;

class Api_Requests {
    const REGISTER_URL = 'https://app.crush.pics/users';
    const API_URL = 'https://api.crush.pics/v1/';

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
        $result = '';
        if ($api_key && $callback_url) {

            $url = self::API_URL . 'callback_urls';
            $ch = curl_init();
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            $headers[] = 'Authorization: Bearer ' . $api_key;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            $data = array('url' => $callback_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }

    public static function update_account($api_key, $compression_type = null, $compression_level_jpg = null, $compression_level_png = null, $compression_level_gif = null, $callback_url = null) {
        $result = '';
        if (!empty($api_key) && ($compression_type || $compression_level_jpg || $compression_level_png || $compression_level_gif || $callback_url)) {
            $url = self::API_URL . 'shop';
            $ch = curl_init();
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            $headers[] = 'Authorization: Bearer ' . $api_key;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');

            $data = array();
            if ($compression_type) {
                $data['compression_type'] = $compression_type;
            }
            if ($compression_level_jpg) {
                $data['compression_level_jpg'] = $compression_level_jpg;
            }
            if ($compression_level_png) {
                $data['compression_level_png'] = $compression_level_png;
            }
            if ($compression_level_gif) {
                $data['compression_level_gif'] = $compression_level_gif;
            }
            if ($callback_url) {
                $data['callback_url'] = $callback_url;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }

    /**
     * create user account request for free plan
     * @param type $email
     * @param type $password_confirmation
     * @param type $password
     * @return type
     */
    public static function registration_request($email, $password_confirmation, $password) {
        $result = '';
        if (!empty($email) && !empty($password_confirmation) && !empty($password)) {
            $data = json_decode('{"user":{"email":"' . $email . '", "password_confirmation":"' . $password_confirmation . '","password":"' . $password . '"}}');
            $ch = curl_init();
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, self::REGISTER_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }

    /**
     *  get user details by valid api key
     * @param type $api_key
     * @return type
     */
    public static function api_key_validation_request($api_key) {
        $result = '';
        if (!empty($api_key)) {


            $url = self::API_URL . 'shop';
            $ch = curl_init();
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            $headers[] = 'Authorization: Bearer ' . $api_key;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }

    /**
     * image compress request
     * @param type $api_key
     * @param type $image_url
     * @param type $compression_type
     * @param type $compression_level
     * @return type
     */
    public static function compress_async_request($api_key, $image_url, $compression_type, $compression_level = null) {
        $result = '';
        if (!empty($api_key) && !empty($image_url) && !empty($compression_type)) {
            if (!empty($compression_level) && $compression_type == 'lossy') {
                $data_string = '{"image_url":"' . $image_url . '", "compression_type":"' . $compression_type . '","compression_level":' . $compression_level . ',"origin":"wp"}';
            } else {
                $data_string = '{"image_url":"' . $image_url . '", "compression_type":"' . $compression_type . '","origin":"wp"}';
            }

            $data = json_decode($data_string);
            $url = self::API_URL . 'original_images';
            $ch = curl_init();
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            $headers[] = 'Authorization: Bearer ' . $api_key;

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
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
        $result = array('status' => '');
        if (!empty($api_key) && !empty($image_url) && !empty($compression_type)) {
            if (!empty($compression_level) && $compression_type == 'lossy') {
                $data_string = '{"image_url":"' . $image_url . '", "compression_type":"' . $compression_type . '","compression_level":' . $compression_level . ',"origin":"wp"}';
            } else {
                $data_string = '{"image_url":"' . $image_url . '", "compression_type":"' . $compression_type . '","origin":"wp"}';
            }

            $data = json_decode($data_string);
            $url = self::API_URL . 'original_images';
            $ch = curl_init();
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            $headers[] = 'Authorization: Bearer ' . $api_key;

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $exec = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['status'] = $httpcode;
            curl_close($ch);
            $result['result'] = $exec;
        }
        return $result;
    }

}
