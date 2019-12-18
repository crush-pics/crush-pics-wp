<?php

if (!defined('ABSPATH'))
    exit;

class Image_Functions {

    protected static $crush_all_process;

    public function __construct() {

    }

    public static function compress_image_webhook_callaback($parameters, $status = '') {
        if (!empty($parameters)) {
            $crushed_id = $parameters['image_id'];
            if (!empty($status) && $status == 'error') {
                $crushed_size = '';
                $crushed_image_link = '';
                $status = 'error';
            } else {
                $crushed_size = $parameters['optimized_images'][0]['size'];
                $crushed_image_link = $parameters['optimized_images'][0]['link'];
                $status = 'crushed';
            }
            if (!empty($crushed_id)) {
                $image_details = self::get_image_by_crushed_id($crushed_id);
                if (!empty($image_details)) {
                    $original_size = $image_details['size'];
                    $src_image_path = $image_details['image_path'];
                    $saved = '';
                    if ($original_size > $crushed_size) {
                        $saved = round((($original_size - $crushed_size) / $original_size) * 100, 2);
                    }
                } else {
                    $original_size = '';
                    $saved = '';
                    $src_image_path = '';
                }
                $compression_backup = get_option('compression_backup');
                if (!$compression_backup) {
                    global $wpdb;
                    $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
                    $wpdb->update($crush_image_actions_table, array('action' => $status, 'saved' => $saved, 'crushed_size' => $crushed_size), array('crushed_id' => $crushed_id), array('%s', '%f', '%d'), array('%d'));
                    //replace img
                    self::image_backup_handle($crushed_image_link, $src_image_path);
                } else {
                    // upload image in new folder then replace it                    
                    $backup_imaged_path = self::image_backup_handle($crushed_image_link, $src_image_path, true);
                    global $wpdb;
                    $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
                    $wpdb->update($crush_image_actions_table, array('action' => $status, 'saved' => $saved, 'crushed_size' => $crushed_size, 'image_backup_path' => $backup_imaged_path), array('crushed_id' => $crushed_id), array('%s', '%f', '%d', '%s'), array('%d'));
                }
            }
        }
    }

    public static function compress_all_image_webhook_callaback($parameters, $status = '') {
        if (!empty($parameters)) {
            $crushed_id = $parameters['image_id'];
            if (!empty($crushed_id)) {
                global $wpdb;
                $table_crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
                $wpdb->delete($table_crush_image_all_sizes, array('crushed_id' => $crushed_id));
                $total_images = get_option('crush_total');
                $uncrushed_count = $wpdb->get_row("select count(id) as uncrushed_count  from $table_crush_image_all_sizes");
                if ($uncrushed_count)
                    $uncrushed_count = $uncrushed_count->uncrushed_count;

                $crushed_ids_count = $wpdb->get_row("select count(crushed_id) as crushed_ids_count  from $table_crush_image_all_sizes where crushed_id > 0");
                if ($crushed_ids_count)
                    $crushed_ids_count = $crushed_ids_count->crushed_ids_count;

                $current_iteration = (int) ($total_images - $uncrushed_count);
                update_option('crush_status', $current_iteration . '_' . $total_images, 'no');
                if ($crushed_ids_count <= 0) {
                    // loop again
                    if ($uncrushed_count > 0) {
                        $limit = 5;
                        $query = "select * from $table_crush_image_all_sizes LIMIT $limit";
                        $images = $wpdb->get_results($query);
                        if (!empty($images)) {
                            foreach ($images as $key => $image) {
                                if (get_option('quota_end') != 'yes') {
                                    $compression_type = get_option('compression_type', 'balanced');
                                    $response = self::compress_image($image->image_id, $image->image_url, $image->image_size, $compression_type);
                                    if (empty($response['crushed_id'])) {
                                        $wpdb->delete($table_crush_image_all_sizes, array('image_id' => $image->image_id, 'image_size' => $image->image_size), array('%d', '%s'));
                                    }
                                } else {
                                    $wpdb->query("TRUNCATE TABLE $table_crush_image_all_sizes");
                                    delete_option('crush_total');
                                    delete_option('crush_start');
                                    update_option('crush_end', 'yes', 'no');
                                }
                            }
                        } else {
                            delete_option('crush_total');
                            delete_option('crush_start');
                            update_option('crush_end', 'yes', 'no');
                        }
                    } else {
                        delete_option('crush_total');
                        delete_option('crush_start');
                        update_option('crush_end', 'yes', 'no');
                    }
                }
            }
        }
    }

    public static function get_crushed_image_details($crushed_id, $size = 'full') {
        $image_details = array();
        if (!empty($crushed_id) && !empty($size)) {
            global $wpdb;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';

            $results = $wpdb->get_results("SELECT *,(select i.image_file_size from $table_image_sizes i where p.image_id = i.image_id and i.image_size = '$size') as image_file_size FROM $crush_image_actions_table p WHERE p.crushed_id = $crushed_id", OBJECT);
            if (!empty($results)) {
                $image_details['size'] = $results[0]->image_file_size;
                $image_details['image_path'] = $results[0]->image_path;
                $image_details['crushed_size'] = $results[0]->crushed_size;
                $image_details['saved'] = $results[0]->saved;
                $image_details['backup_image'] = $results[0]->image_backup_path;
                $image_details['image_id'] = $results[0]->image_id;
                $image_details['action'] = $results[0]->action;
                $image_details['image_size'] = $results[0]->image_size;
                $image_details['compression_type'] = $results[0]->compression_type;
            }
        }
        return $image_details;
    }

    public static function get_image_by_crushed_id($crushed_id) {
        $image_details = array();
        if (!empty($crushed_id)) {
            global $wpdb;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';

            $results = $wpdb->get_results("SELECT * FROM $crush_image_actions_table p WHERE p.crushed_id = $crushed_id", OBJECT);
            if (!empty($results)) {
                $image_size = $results[0]->image_size;
                $image_details['image_path'] = $results[0]->image_path;
                $image_details['crushed_size'] = $results[0]->crushed_size;
                $image_details['saved'] = $results[0]->saved;
                $image_details['backup_image'] = $results[0]->image_backup_path;
                $image_details['image_id'] = $results[0]->image_id;
                $image_details['action'] = $results[0]->action;
                $image_details['image_size'] = $results[0]->image_size;
                $image_details['compression_type'] = $results[0]->compression_type;
                $image_path_results = $wpdb->get_results("SELECT image_file_size FROM $table_image_sizes p where p.image_id = " . $image_details['image_id'] . " and p.image_size = '$image_size'", OBJECT);
                if (!empty($image_path_results)) {
                    $image_details['size'] = $image_path_results[0]->image_file_size;
                }
            }
        }
        return $image_details;
    }

    public static function get_sized_image_details($image_id, $size = 'full') {
        $image_details = array();
        if (!empty($image_id) && !empty($size)) {
            global $wpdb;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
            $results = $wpdb->get_results("SELECT *,(select i.image_file_size from $table_image_sizes i where p.image_id = i.image_id and i.image_size = '$size') as image_file_size FROM $crush_image_actions_table p WHERE p.image_id = $image_id AND p.is_history = 0 AND p.image_size='$size'", OBJECT);
            if (!empty($results)) {
                $image_details['image_path'] = $results[0]->image_path;
                $image_details['original_size'] = $results[0]->image_file_size;
                $image_details['image_id'] = $results[0]->image_id;
                $image_details['crushed_size'] = $results[0]->crushed_size;
                $image_details['saved'] = $results[0]->saved;
                $image_details['action'] = $results[0]->action;
                $image_details['compression_type'] = $results[0]->compression_type;
                $image_details['backup_image'] = $results[0]->image_backup_path;
                $image_details['image_size'] = $results[0]->image_size;
            }
        }
        return $image_details;
    }

    public static function get_sized_image_history($image_id, $size = 'full') {
        $image_history = array();
        if (!empty($image_id) && !empty($size)) {
            $image_post = get_post($image_id);
            $image_time = date('M d, Y.G:i', strtotime($image_post->post_date)); //Oct 3, 2019.18:06
            $image_history[$image_time][] = __('Imported to Wordpress', 'wp-image-compression');
            global $wpdb;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $results = $wpdb->get_results("SELECT * FROM $crush_image_actions_table WHERE image_id = $image_id AND image_size = '$size' ORDER BY created_at ASC", OBJECT);
            if (!empty($results)) {
                foreach ($results as $key => $result) {
                    if ($result->action == 'crushed')
                        $status = __('Compressed', 'wp-image-compression') . ' (' . ucfirst($result->compression_type) . ')';
                    elseif ($result->action == 'error')
                        $status = __('Already compressed', 'wp-image-compression');
                    else
                        $status = ucfirst($result->action);

                    $time = date('M d, Y.G:i', strtotime($result->created_at));
                    $image_history[$time][] = $status;
                }
            }
        }
        return $image_history;
    }

    public static function check_image_compress($crushed_id) {
        $response = array();
        if (!empty($crushed_id)) {
            $image_details = self::get_crushed_image_details($crushed_id);
            if (!empty($image_details) && !empty($image_details['action'])) {
                if ($image_details['action'] == 'error') {
                    $response['status'] = $image_details['action'];
                } elseif ($image_details['action'] == 'crushed') {
                    $response['status'] = $image_details['action'];
                    if (!empty($image_details['backup_image'])) {
                        $compression_backup = get_option('compression_backup');
                        if (!empty($compression_backup)) {
                            $upload_dir = wp_upload_dir();
                            $response['backup'] = 'yes';
                            $response['image_backup_path'] = $image_details['backup_image'];
                            $response['image_url'] = $upload_dir['baseurl'] . $image_details['image_path'];
                        }
                    } else {
                        $response['backup'] = 'no';
                    }
                    $response['saved'] = ceil($image_details['saved']) . '% ' . __('SAVED', 'wp-image-compression');
                }
                $response['image_id'] = $image_details['image_id'];
                $response['image_size'] = $image_details['image_size'];
            }
        }
        return $response;
    }

    public static function check_image_status($id, $size) {
        $response = array();
        if (!empty($id) && !empty($size)) {
            $image_details = self::get_sized_image_details($id, $size);
            if (!empty($image_details) && !empty($image_details['action'])) {
                if ($image_details['action'] == 'crushed') {
                    $response['status'] = $image_details['action'];
                    if (!empty($image_details['backup_image'])) {
                        $compression_backup = get_option('compression_backup');
                        if (!empty($compression_backup)) {
                            $upload_dir = wp_upload_dir();
                            $response['backup'] = 'yes';
                            $response['image_id'] = $image_details['image_id'];
                            $response['image_backup_path'] = $image_details['backup_image'];
                            $response['image_url'] = $upload_dir['baseurl'] . $image_details['image_path'];
                        }
                    } else {
                        $response['backup'] = 'no';
                    }
                    $response['saved'] = $image_details['saved'] . '% ' . __('SAVED', 'wp-image-compression');
                } else {
                    $response['status'] = $image_details['action'];
                }
            }
        }
        return $response;
    }

    public static function compress_image($id = '', $image_url = '', $size = '', $compression_type = '') {
        $response = array();
        if (!empty($id) && !empty($image_url) && !empty($size) && !empty($compression_type)) {
            $upload_dir = wp_upload_dir();
            $image_path = str_replace($upload_dir['baseurl'], '', $image_url);
            $api_key = get_option('wpic_api_key');
            if ($api_key) {
                $compression_level = null;
                //image type
                $image_mime_type = get_post_mime_type($id);
                if ($image_mime_type) {
                    $type_data = explode("/", $image_mime_type);
                    $type = end($type_data);
                    if ($type)
                        $compression_level = get_option("compression_type_custom_$type");
                }
                $results_json = Api_Requests::compress_request($api_key, $image_url, $compression_type, $compression_level);
                $results = json_decode($results_json['result'], true);
                if ($results_json['status'] == 402) {
                    update_option('quota_end', 'yes', 'no');
                } else {
                    delete_option('quota_end');
                }
                $response['quota_status'] = get_option('quota_end');
                if (!empty($results['original_image']) && $results['success']) {
                    $crushed_id = $results['original_image']['id'];
                    $status = $results['original_image']['status'];
                    $created_at = $results['original_image']['created_at'];
                    $original_size = $results['original_image']['size'];
                    global $wpdb;
                    if (!empty($id) && !empty($size) && !empty($status) && !empty($created_at) && !empty($crushed_id) && !empty($original_size)) {
                        if (get_option('crush_start') == 'yes') {
                            $table_crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
                            $wpdb->update($table_crush_image_all_sizes, array('crushed_id' => $crushed_id), array('image_id' => $id, 'image_size' => $size), array('%d'), array('%d', '%s'));
                        }
                        $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
                        $wpdb->update($crush_image_actions_table, array('is_history' => 1), array('image_id' => $id, 'image_size' => $size), array('%d'), array('%d', '%s'));
                        $wpdb->insert($crush_image_actions_table, array('crushed_id' => $crushed_id, 'image_id' => $id, 'image_size' => $size, 'image_path' => $image_path, 'action' => $status, 'created_at' => $created_at, 'saved' => '', 'is_history' => 0, 'original_size' => $original_size, 'crushed_size' => '', 'compression_type' => $compression_type, 'image_backup_path' => '', 'exif_data' => ''));
                        $response['crushed_id'] = $crushed_id;
                    }
                } elseif (isset($results['formatted_message'])) {
                    $response['error'] = $results['formatted_message'];
                }
            }
        }
        return $response;
    }

    public static function restore_image($id = '', $image_url = '', $size, $backup_image = '') {
        $response = array();
        if (!empty($id) && !empty($image_url) && !empty($size) && !empty($backup_image)) {
            global $wpdb;
            $upload_dir = wp_upload_dir();
            //$image_path = '';
            $image_path = str_replace($upload_dir['baseurl'], '', $image_url);
            // update image id historical
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $wpdb->update($crush_image_actions_table, array('is_history' => 1), array('image_id' => $id, 'image_size' => $size), array('%d'), array('%d', '%s'));
            //insert restored image
            self:: insert_restored_image($id, $size, 'restored', $backup_image);
            //replace img
            $backup_image_path = $upload_dir['basedir'] . $backup_image;
            $current_image_path = $upload_dir['basedir'] . $image_path;
            unlink($current_image_path);
            copy($backup_image_path, $current_image_path);
            unlink($backup_image_path);

            $response['image_id'] = $id;
            $response['image_url'] = $image_url;
        }
        return $response;
    }

    public static function image_backup_handle($compressed_image_url = '', $image_url = '', $backup = false) {
        $backup_imaged_path = '';
        if (!empty($compressed_image_url) && !empty($image_url)) {
            $upload_dir = wp_upload_dir();
            $path = $upload_dir['basedir'] . $image_url;
            $compressed_image_data = self::get_image_data($compressed_image_url);
            if ($backup) {
                $upload_dir = wp_upload_dir();
                $filename = basename($path);
                if (wp_mkdir_p($upload_dir['basedir'] . '/crushed-backup/')) {
                    $file = $upload_dir['basedir'] . '/crushed-backup/' . $filename;
                } else {
                    $file = $upload_dir['basedir'] . '/crushed-backup/' . $filename;
                }
                $backup_imaged_path = '/crushed-backup/' . $filename;
                copy($path, $file);
                file_put_contents($path, $compressed_image_data);
            } else {
                unlink($path);
                file_put_contents($path, $compressed_image_data);
            }
        }
        return $backup_imaged_path;
    }

    public static function get_image_data($image_url) {
        $image_data = '';
        if (!empty($image_url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $image_url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            $image_data = curl_exec($ch);
            curl_close($ch);
        }
        return $image_data;
    }

    public static function insert_restored_image($id = '', $size = '', $status = '', $backup_image = '') {
        global $wpdb;
        if (!empty($id) && !empty($size) && !empty($status) && !empty($backup_image)) {
            $timestamp = date('Y-m-d G:i:s');
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $wpdb->insert($crush_image_actions_table, array('crushed_id' => '', 'image_id' => $id, 'image_size' => $size, 'image_path' => $backup_image, 'action' => $status, 'created_at' => $timestamp, 'saved' => '', 'is_history' => 0, 'original_size' => '', 'crushed_size' => '', 'compression_type' => '', 'image_backup_path' => '', 'exif_data' => ''));
        }
    }

    public static function get_image_items($image_id) {
        $image_items = array();
        $compression_sizes = get_option('compression_sizes');
        if (!empty($image_id) && !empty($compression_sizes)) {
            global $wpdb;
            $crush_image_sizes_table = $wpdb->prefix . 'crush_image_sizes';
            $images_type = implode("','", $compression_sizes);
            $image_items = $wpdb->get_results("select p.image_id,p.image_size,p.image_size_path
                            from $crush_image_sizes_table p
                            where p.image_size IN ('$images_type') AND p.image_id = $image_id ");
        }
        return $image_items;
    }

    public static function get_image_item_original_size($image_id, $size) {
        $image_original_size = '';
        if (!empty($image_id) && !empty($size)) {
            global $wpdb;
            $crush_image_sizes_table = $wpdb->prefix . 'crush_image_sizes';
            $image_items = $wpdb->get_results("select p.image_id,p.image_size,p.image_size_path,p.image_file_size    
                            from $crush_image_sizes_table p
                            where  p.image_size = '$size' AND p.image_id = $image_id ");
            if (!empty($image_items)) {
                $image_original_size = $image_items[0]->image_file_size;
            }
        }
        return $image_original_size;
    }

    public static function get_crushed_images_by_id($image_id) {
        $results = array();
        if (!empty($image_id)) {
            global $wpdb;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $results = $wpdb->get_results("SELECT * FROM $crush_image_actions_table WHERE image_id = $image_id", OBJECT);
        }
        return $results;
    }

    public static function is_action_exist($image_id, $size, $action) {
        $return = false;
        if (!empty($image_id) && !empty($size) && !empty($action)) {
            global $wpdb;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $results = $wpdb->get_results("SELECT * FROM $crush_image_actions_table WHERE image_id = $image_id AND image_size = '$size' AND action = '$action'", OBJECT);
            if (!empty($results) && count($results) > 0) {
                $return = true;
            }
        }
        return $return;
    }

    public static function format_size_units($bytes) {
        $result = '-';
        if (!empty($bytes)) {
            if ($bytes >= 1073741824) {
                $result = round($bytes / 1073741824) . ' ' . __('GB', 'wp-image-compression');
            } elseif ($bytes >= 1048576) {
                $result = round($bytes / 1048576) . ' ' . __('MB', 'wp-image-compression');
            } elseif ($bytes >= 1024) {
                $result = round($bytes / 1024) . ' ' . __('KB', 'wp-image-compression');
            } elseif ($bytes > 1) {
                $result = round($bytes) . ' ' . __('bytes', 'wp-image-compression');
            } elseif ($bytes == 1) {
                $result = round($bytes) . ' ' . __('byte', 'wp-image-compression');
            } else {
                $result = '0' . __('bytes', 'wp-image-compression');
            }
        }

        return $result;
    }

    public static function size_from_bytes($bytes) {
        $result = '-';
        if (!empty($bytes)) {
            if ($bytes >= 1073741824) {
                $result = round($bytes / 1073741824) . '_GB';
            } elseif ($bytes >= 1048576) {
                $result = round($bytes / 1048576) . '_MB';
            } elseif ($bytes >= 1024) {
                $result = round($bytes / 1024) . '_KB';
            } elseif ($bytes > 1) {
                $result = round($bytes) . '_bytes';
            } elseif ($bytes == 1) {
                $result = round($bytes) . '_byte';
            } else {
                $result = '0';
            }
        }
        return $result;
    }

    public static function size_by_format($format, $bytes) {
        $result = '-';
        if (!empty($format) && !empty($bytes)) {
            if ($format == 'GB') {
                $result = round($bytes / 1073741824);
            } elseif ($format == 'MB') {
                $result = round($bytes / 1048576);
            } elseif ($format == 'KB') {
                $result = round($bytes / 1024);
            } elseif ($format == 'bytes') {
                $result = round($bytes);
            } elseif ($format == 'byte') {
                $result = round($bytes);
            } else {
                $result = '0';
            }
        }
        return $result;
    }

    public static function size_to_bytes($bytes) {
        $result = '-';
        if (!empty($bytes)) {
            $data = explode('_', $bytes);
            if ($data[1] == 'GB') {
                $result = round($data[0] * 1073741824);
            } elseif ($data[1] == 'MB') {
                $result = round($data[0] * 1048576);
            } elseif ($data[1] == 'KB') {
                $result = round($data[0] * 1024);
            } elseif ($data[1] == 'bytes') {
                $result = round($data[0]);
            } elseif ($data[1] == 'byte') {
                $result = round($data[0]);
            } else {
                $result = '0';
            }
        }

        return $result;
    }

    public static function get_saved_size($original_size, $crushed_size) {
        $saved = '0';
        if (!empty($original_size) && !empty($crushed_size)) {
            $original_size_data = explode('_', $original_size);
            $crushed_size_data = explode('_', $crushed_size);
            if ($original_size_data[1] != $crushed_size_data[1]) {
                $original_size_bytes = self::size_to_bytes($original_size);
                $original_size_by_format = self::size_by_format($crushed_size_data[1], $original_size_bytes);
                $saved_value = (int) $original_size_by_format - (int) $crushed_size_data[0];
                $saved = $saved_value . ' ' . $crushed_size_data[1];
                if ($saved_value >= 1024) {
                    $saved = round($saved_value / 1024) . ' ' . $original_size_data[1];
                }
            } else {
                $saved_value = (int) $original_size_data[0] - (int) $crushed_size_data[0];
                $saved = $saved_value . ' ' . $crushed_size_data[1];                
            }
        }

        return $saved;
    }

    public static function fill_image_details($id, $type, $size) {
        $response = array();
        if (!empty($id) && !empty($type) && !empty($size)) {
            $crushed_size = '';
            $saved = '';
            $compression_type = '';
            $history_html = '';
            $image_id = '';

            if (!empty($type) && $type == 'image_id') {
                $image_sized_details = self::get_sized_image_details($id, $size);
                if (empty($image_sized_details['crushed_size'])) {
                    $crushed_size = '-';
                    $saved = '-';
                } else {
                    $original_size = $image_sized_details['original_size'];
                    $crushed_size = self::format_size_units($image_sized_details['crushed_size']);
//                    $saved_value = (int) self::size_units($original_size) - (int) self::size_units($image_sized_details['crushed_size']);
                    $saved = self::get_saved_size(self::size_from_bytes($original_size), self::size_from_bytes($image_sized_details['crushed_size']));
                }
                $image_id = $id;
                $compression_type = $image_sized_details['compression_type'];
                $file_size = $image_sized_details['original_size'];
            } elseif (!empty($type) && $type == 'crush_id') {
                $image_crushed_details = self::get_crushed_image_details($id, $size);
                if (empty($image_crushed_details['crushed_size'])) {
                    $crushed_size = '-';
                    $saved = '-';
                } else {
                    $original_size = $image_crushed_details['size'];
                    $crushed_size = self::format_size_units($image_crushed_details['crushed_size']);
                    //$saved_value = (int) self::size_units($original_size) - (int) self::size_units($image_crushed_details['crushed_size']);
                    $saved = self::get_saved_size(self::size_from_bytes($original_size), self::size_from_bytes($image_crushed_details['crushed_size']));
                }
                $compression_type = $image_crushed_details['compression_type'];
                $image_id = $image_crushed_details['image_id'];
                $file_size = $image_crushed_details['size'];
            }

            global $wpdb;
            $images_count = 0;
            $crushed_images_count = 0;
            //check if api activated
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
            //total images count and crushed images count
            $enabled_sizes = get_option('compression_sizes');
            if (!$enabled_sizes)
                $enabled_sizes = array('full');
            else
                $enabled_sizes[] = 'full';

            $images_count_res = $wpdb->get_row('select count(id) as images_count from ' . $table_image_sizes . ' where image_size in ("' . implode('", "', $enabled_sizes) . '")');
            if ($images_count_res)
                $images_count = $images_count_res->images_count;


            $crushed_images_count_res = $wpdb->get_row('select count(id) as crushed_images_count from ' . $crush_image_actions_table . ' where action  IN ("crushed","error") and is_history = 0 and image_size in ("' . implode('", "', $enabled_sizes) . '")');
            if ($crushed_images_count_res)
                $crushed_images_count = $crushed_images_count_res->crushed_images_count;

            $crushed_no = self::get_number_crushed_size_images($image_id);
            $total_size_no = self::get_total_number_crushed_size_images($image_id);

            $response['total_images_no'] = number_format($images_count);
            $response['crushed_images_no'] = number_format($crushed_images_count);
            $response['un_crushed_images_no'] = number_format($images_count - $crushed_images_count);

            $response['crushed_no'] = $crushed_no;
            $response['total_size_no'] = $total_size_no;

            $response['crushed_size'] = $crushed_size;
            $response['saved'] = $saved;
            $response['compression_type'] = self::get_compression_type_text($compression_type);

            if (!empty($image_id)) {
                $image_history = self::get_sized_image_history($image_id, $size);
                $history_html = '';
                if (!empty($image_history)) {
                    foreach ($image_history as $key => $history) {
                        if (!empty($history) && is_array($history)) {
                            foreach ($history as $value) {
                                $history_html .= '<li><span>' . $value . '</span><span class="text-muted d-block">' . $key . '</span></li>';
                            }
                        } else {
                            $history_html .= '<li><span>' . $history . '</span><span class="text-muted d-block">' . $key . '</span></li>';
                        }
                    }
                }
                $response['history_html'] = $history_html;
                $response['image_id'] = $image_id;
            }
        }
        return $response;
    }

    public static function get_compression_type_text($compression_type = '') {
        if (empty($compression_type)) {
            $compression_type = get_option('compression_type', '-');
        }
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
        return $compression_type;
    }

    public static function get_image_size_format($image_name) {
        $image_pixel = '';
        if (!empty($image_name)) {
            $image_name_data = explode('-', $image_name);
            $image_pixel_extention = end($image_name_data);
            $image_pixel_data = explode('.', $image_pixel_extention);
            $image_pixel_data = str_replace('x', ' x ', $image_pixel_data[0]);
            $image_pixel = '( ' . $image_pixel_data . __(' pixels', 'wp-image-compression') . ' )';
        }
        return $image_pixel;
    }

    public static function list_all_full_image($search_term = '', $offest = 0, $limit = 0) {
        global $wpdb;
        $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
        $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
        $query = "select p.ID,p.guid,p.post_date,(select i.saved from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as saved
                ,(select i.image_backup_path from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as backup_image
                ,(select i.action from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as status 
                ,(select i.image_file_size from $table_image_sizes i where p.ID = i.image_id and i.image_size = 'full') as image_file_size
                from $wpdb->posts p
                where
                p.post_type = 'attachment' and
                p.post_status = 'inherit' and
                (p.post_mime_type = 'image/jpeg' OR p.post_mime_type = 'image/gif' OR p.post_mime_type = 'image/png')
                ";
        if (!empty($search_term)) {
            $term_slash = str_replace("\'", " ", $search_term);
            $query .= " and p.guid LIKE '%" . $wpdb->esc_like($term_slash) . "%'";
        }
        $order = "ORDER BY p.post_date DESC ";
        $pagenation = '';
        if (!empty($limit) && $offest >= 0) {
            $pagenation = "LIMIT $offest, $limit";
        }
        $sql = $query . $order . $pagenation;
        $images = $wpdb->get_results($sql);

        return $images;
    }

    public static function site_image_sizes_handling($offest = 0, $limit = 15) {

        global $wpdb;

        //check for removed images while plugin not active
        $crush_image_sizes = $wpdb->prefix . 'crush_image_sizes';
        $deleted_images_array = array();
        $deleted_img_query = "SELECT DISTINCT(image_id) FROM $crush_image_sizes
                WHERE image_id not in (select ID from $wpdb->posts WHERE post_type = 'attachment' 
                and post_status = 'inherit' and 
                (post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif' OR post_mime_type = 'image/png'))";
        $deleted_images = $wpdb->get_results($deleted_img_query);
        foreach ($deleted_images as $deleted_image) {
            $deleted_images_array[] = $deleted_image->image_id;
        }

        if ($deleted_images_array) {
            $deleted_images_ids = implode(',', $deleted_images_array);
            $crush_image_actions = $wpdb->prefix . 'crush_image_actions';
            $crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';

            $wpdb->query("delete from $crush_image_sizes where image_id in ($deleted_images_ids)");
            $wpdb->query("delete from $crush_image_actions where image_id in ($deleted_images_ids)");
            $wpdb->query("delete from $crush_image_all_sizes where image_id in ($deleted_images_ids)");
        }


        $query = "select p.ID,p.guid,p.post_date,pm.meta_value 
                from $wpdb->posts p
                left join $wpdb->postmeta pm ON p.ID = pm.post_id
                where
                p.post_type = 'attachment' and
                p.post_status = 'inherit' and
                (p.post_mime_type = 'image/jpeg' OR p.post_mime_type = 'image/gif' OR p.post_mime_type = 'image/png')
                and pm.meta_key = '_wp_attachment_metadata'
                ";
        if (!empty($limit)) {
            $pagenation = "LIMIT $offest, $limit";
        }
        $sql = $query . $pagenation;
        $images = $wpdb->get_results($sql);
        if (!empty($images)) {
            foreach ($images as $image) {
                $attachment_id = $image->ID;
                if (!empty($image->meta_value)) {
                    $metadata = unserialize($image->meta_value);
                    $insert_image_sql_data = self::insert_image_sizes($attachment_id, $metadata);
                    if (!empty($insert_image_sql_data) && !empty($insert_image_sql_data['sql'])) {
                        $wpdb->query($insert_image_sql_data['sql']);
                    }
                    if (!empty($insert_image_sql_data) && !empty($insert_image_sql_data['sizes'])) {
                        $old_crushed_sizes = Image_Functions::get_image_crushed_sizes($attachment_id);
                        foreach ($insert_image_sql_data['sizes'] as $size => $image_url) {
                            //compress image if automated
                            self::automatic_compress_uploaded_image($attachment_id, $image_url, $size, $old_crushed_sizes);
                        }
                    }
                }
            }
            $next_offest = $offest + $limit;
            self::site_image_sizes_handling($next_offest, $limit);
        }
    }

    public static function insert_image_sizes($attachment_id, $data) {
        $sql_data = array();
        if (!empty($attachment_id) && !empty($data)) {
            global $wpdb;
            $upload_dir = wp_upload_dir();
            $missed_sizes = array();
            //full image path and size
            $full_image_path = $upload_dir['basedir'] . '/' . $data['file'];
            $full_image_url = $upload_dir['baseurl'] . '/' . $data['file'];
            $full_image_file_size = filesize($full_image_path);
            $full_image_name = basename($full_image_path);
            // general dir
            $base_dir_path = str_replace($full_image_name, '', $full_image_path);
            $base_dir_url = str_replace($full_image_name, '', $full_image_url);
            // insert full image 
            $image_sizes_table = $wpdb->prefix . 'crush_image_sizes';
            $old_sizes = self::get_image_meta_sizes($attachment_id);
            $sql = "";
            if (!in_array('full', $old_sizes)) {
                $missed_sizes['full'] = $full_image_url;
                if (empty($sql)) {
                    $sql = "INSERT INTO $image_sizes_table (image_id, image_size, image_size_path,image_file_size) VALUES ";
                }
                $sql .= "('" . $attachment_id . "','full','" . $full_image_url . "','" . $full_image_file_size . "')";
            }
            if (!empty($data['sizes'])) {
                //get last element in array
                $sized_file_path = '';
                $sized_file_size = '';
                foreach ($data['sizes'] as $key => $size) {
                    //sized image path and size
                    $sized_file_path = $base_dir_path . $size['file'];
                    $sized_file_url = $base_dir_url . $size['file'];
                    $sized_file_size = filesize($sized_file_path);
                    // insert image sizes
                    if (!in_array($key, $old_sizes)) {
                        $missed_sizes[$key] = $sized_file_url;
                        if (empty($sql)) {
                            $sql = "INSERT INTO $image_sizes_table (image_id, image_size, image_size_path,image_file_size) VALUES ";
                        } else {
                            $sql .= ",";
                        }
                        $sql .= "('" . $attachment_id . "','" . $key . "','" . $sized_file_url . "','" . $sized_file_size . "')";
                    }
                }
            }
            $sql_data['sql'] = $sql;
            $sql_data['sizes'] = $missed_sizes;
        }
        return $sql_data;
    }

    public static function get_image_meta_sizes($image_id = '') {
        $sizes = array();
        if (!empty($image_id)) {
            global $wpdb;
            $crush_image_sizes_table = $wpdb->prefix . 'crush_image_sizes';
            $results = $wpdb->get_results("SELECT * FROM $crush_image_sizes_table WHERE image_id = $image_id", OBJECT);
            if (!empty($results) && count($results) > 0) {
                foreach ($results as $result) {
                    $sizes[] = $result->image_size;
                }
            }
        }
        return $sizes;
    }

    public static function get_image_crushed_sizes($image_id = '') {
        $sizes = array();
        if (!empty($image_id)) {
            global $wpdb;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $results = $wpdb->get_results("SELECT * FROM $crush_image_actions_table WHERE image_id = $image_id", OBJECT);
            if (!empty($results) && count($results) > 0) {
                foreach ($results as $result) {
                    $sizes[] = $result->image_size;
                }
            }
        }
        return $sizes;
    }

    public static function automatic_compress_uploaded_image($attachment_id, $image_url, $size, $old_crushed_sizes) {

        $compression_auto = get_option('compression_auto');
        $compression_sizes = get_option('compression_sizes');
        $compression_type = get_option('compression_type', 'balanced');

        if (empty($compression_sizes))
            $compression_sizes = array();
        if (empty($old_crushed_sizes))
            $old_crushed_sizes = array();
        if (!empty($compression_auto) && !empty($attachment_id) && !empty($image_url) && !empty($size)) {
            if (($size == 'full' || (!empty($compression_sizes) && in_array($size, $compression_sizes))) && !in_array($size, $old_crushed_sizes)) {
                self::compress_image($attachment_id, $image_url, $size, $compression_type);
            }
        }
    }

    public static function image_check_compress_all() {
        $response = array();
        $status_data = get_option('crush_status');
        $iteration_data = explode('_', $status_data);
        $iteration = $iteration_data[0];
        $total = $iteration_data[1];
        $percent = intval($iteration / $total * 100) . "%";
        $response['iteration'] = $iteration;
        $response['total'] = $total;
        $response['percent'] = $percent;
        $response['quota_status'] = get_option('quota_end');

        if (!empty(get_option('crush_stop'))) {
            $response['status'] = 'stop';
            $response['url'] = admin_url() . 'admin.php?page=crush-pics';
            delete_option('crush_stop');
        } elseif (!empty(get_option('crush_start'))) {
            $response['status'] = 'start';
        } elseif (!empty(get_option('crush_end'))) {
            global $wpdb;
            $images_count = 0;
            $crushed_images_count = 0;
            //check if api activated
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
            //total images count and crushed images count
            $enabled_sizes = get_option('compression_sizes');
            if (!$enabled_sizes)
                $enabled_sizes = array('full');
            else
                $enabled_sizes[] = 'full';

            $images_count_res = $wpdb->get_row('select count(id) as images_count from ' . $table_image_sizes . ' where image_size in ("' . implode('", "', $enabled_sizes) . '")');
            if ($images_count_res)
                $images_count = $images_count_res->images_count;


            $crushed_images_count_res = $wpdb->get_row('select count(id) as crushed_images_count from ' . $crush_image_actions_table . ' where action IN ("crushed","error") and is_history = 0 and image_size in ("' . implode('", "', $enabled_sizes) . '")');
            if ($crushed_images_count_res)
                $crushed_images_count = $crushed_images_count_res->crushed_images_count;

            $response['un_crushed_images_no'] = number_format($images_count - $crushed_images_count);
            $response['status'] = 'end';
            $response['url'] = admin_url() . 'admin.php?page=crush-pics';
            delete_option('crush_status');
            delete_option('crush_end');
        }
        return $response;
    }

    public static function handle_crush_all() {

        global $wpdb;
        $total = 0;
        $enabled_sizes = get_option('compression_sizes');
        if (!$enabled_sizes)
            $enabled_sizes = array('full');
        else
            $enabled_sizes[] = 'full';
        $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
        $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
        $query = "select * from $table_image_sizes where image_size in ('" . implode("','", $enabled_sizes) . "') and (image_id,image_size) NOT IN (select image_id,image_size from  $crush_image_actions_table where action IN ('crushed','error') and is_history = 0)";
        $un_crushed_images = $wpdb->get_results($query);
        self::$crush_all_process = new WP_Crush_All_Process();
        if (!empty($un_crushed_images)) {
            update_option('crush_start', 'yes', 'no');
            $total = count($un_crushed_images);
            foreach ($un_crushed_images as $key => $not_crushed_image) {
                self::$crush_all_process->push_to_queue(array('image_id' => $not_crushed_image->image_id, 'image_size' => $not_crushed_image->image_size, 'image_url' => $not_crushed_image->image_size_path, 'image_total' => $total, 'image_iteration' => $key));
            }
            self::$crush_all_process->save()->dispatch();
        }

        wp_redirect(admin_url('admin.php?page=crush-pics'));
        exit;
    }

    public static function get_number_crushed_size_images($image_id) {
        $crushed_no = 0;
        if (!empty($image_id)) {
            global $wpdb;
            $enabled_sizes = get_option('compression_sizes');
            if (!$enabled_sizes)
                $enabled_sizes = array('full');
            else
                $enabled_sizes[] = 'full';
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $query = "select * from $crush_image_actions_table where image_size in ('" . implode("','", $enabled_sizes) . "') and action IN ('crushed','error') and is_history = 0 and image_id = $image_id";
            $results = $wpdb->get_results($query);
            $crushed_no = count($results);
        }
        return $crushed_no;
    }

    public static function get_total_number_crushed_size_images($image_id) {
        $total_versions_no = 0;
        if (!empty($image_id)) {
            global $wpdb;
            $enabled_sizes = get_option('compression_sizes');
            if (!$enabled_sizes)
                $enabled_sizes = array('full');
            else
                $enabled_sizes[] = 'full';

            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
            $query = "select * from $table_image_sizes where image_size in ('" . implode("','", $enabled_sizes) . "') and image_id = $image_id ";
            $results = $wpdb->get_results($query);
            $total_versions_no = count($results);
        }
        return $total_versions_no;
    }

}