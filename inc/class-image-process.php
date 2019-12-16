<?php

if (!defined('ABSPATH'))
    exit;

class Image_Process {

    public function __construct() {
        add_action('delete_attachment', array($this, 'delete_image_sizes'), 10);
        add_filter('wp_update_attachment_metadata', array($this, 'regenerate_image_sizes'), 10, 2);
    }

    public function delete_image_sizes($post_id) {
        if (wp_attachment_is_image($post_id)) {
            global $wpdb;
            $metadata = wp_get_attachment_metadata($post_id);
            if (!empty($metadata)) {
                $upload_dir = wp_upload_dir();
                //full image path and size
                $full_image_path = $upload_dir['basedir'] . '/' . $metadata['file'];
                $full_image_name = basename($full_image_path);
                $full_path = $upload_dir['basedir'] . '/crushed-backup/' . $full_image_name;
                if (file_exists($full_path))
                    @unlink($full_path);
                if (!empty($metadata['sizes'])) {
                    foreach ($metadata['sizes'] as $key => $size) {
                        $sized_file_path = $upload_dir['basedir'] . '/crushed-backup/' . $size['file'];
                        if (file_exists($sized_file_path))
                            @unlink($sized_file_path);
                    }
                }
            }
            //delete image sizes from image sizes table
            $image_sizes_table = $wpdb->prefix . 'crush_image_sizes';
            $wpdb->delete($image_sizes_table, array('image_id' => $post_id));
            //delete image sizes from image action table
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $wpdb->delete($crush_image_actions_table, array('image_id' => $post_id));
        }
    }

    public function regenerate_image_sizes($data, $attachment_id) {
        if (!empty($data)) {
            global $wpdb;
            $sql_data = Image_Functions::insert_image_sizes($attachment_id, $data);
            if (!empty($sql_data) && !empty($sql_data['sql'])) {
                $wpdb->query($sql_data['sql']);
            }
            if (!empty($sql_data) && !empty($sql_data['sizes'])) {
                $old_crushed_sizes = Image_Functions::get_image_crushed_sizes($attachment_id);
                foreach ($sql_data['sizes'] as $size => $image_url) {
                    //compress image if automated
                    Image_Functions::automatic_compress_uploaded_image($attachment_id, $image_url, $size, $old_crushed_sizes);
                }
            }
            return $data;
        }
    }

}
