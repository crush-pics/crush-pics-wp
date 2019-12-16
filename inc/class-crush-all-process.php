<?php

if (!class_exists('WP_Async_Request', false)) {
    include_once WPIC_PATH . 'inc/wp-async-request.php';
}

if (!class_exists('WP_Background_Process', false)) {
    include_once WPIC_PATH . 'inc/wp-background-process.php';
}

class WP_Crush_All_Process extends WP_Background_Process {

    /**
     * @var string
     */
    protected $action = 'crush_all_process';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task($item) {
        global $wpdb;
        $id = $item['image_id'];
        $size = $item['image_size'];
        $image_url = $item['image_url'];
        $total = $item['image_total'];
        update_option('crush_total', $total, 'no');
        $table_crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
        $var = $wpdb->insert($table_crush_image_all_sizes, array(
            'image_id' => $id,
            'image_size' => $size,
            'image_url' => $image_url,
                ), array('%d', '%s', '%s'));
        return false;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete() {
        parent::complete();
        global $wpdb;
        $total = get_option('crush_total');
        $crush_loop_no = ceil(($total / 5));
        update_option('crush_loop_no', $crush_loop_no, 'no');
        $table_crush_image_all_sizes = $wpdb->prefix . 'crush_image_all_sizes';
        $query = "select * from $table_crush_image_all_sizes LIMIT 0,5";

        $images = $wpdb->get_results($query);
        update_option('crush_images', $images, 'no');
        if (!empty($images)) {
            foreach ($images as $key => $image) {
                if (get_option('quota_end') != 'yes') {
                    $compression_type = get_option('compression_type', 'balanced');
                    $response = Image_Functions::compress_image($image->image_id, $image->image_url, $image->image_size, $compression_type);

                    if (empty($response['crushed_id'])) {
                        $wpdb->delete($table_crush_image_all_sizes, array('image_id' => $image->image_id, 'image_size' => $image->image_size), array('%d', '%s'));
                    }
                    if ($total == 1 && empty($response['crushed_id'])) {
                        delete_option('crush_total');
                        delete_option('crush_loop_no');
                        delete_option('crush_start');
                        update_option('crush_end', 'yes', 'no');
                    }
                } else {
                    $wpdb->query("TRUNCATE TABLE $table_crush_image_all_sizes");
                    delete_option('crush_total');
                    delete_option('crush_loop_no');
                    delete_option('crush_start');
                    update_option('crush_end', 'yes', 'no');
                }
            }
        }
    }

}
