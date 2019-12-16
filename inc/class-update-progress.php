<?php

class Update_Progress {

    public function __construct() {
        add_action('wp_image_compression_progressbar', array($this, 'wp_image_compression_progressbar'));
    }

    public function crush_ll_images() {
        if (!empty($_GET['action']) && $_GET['action'] == 'crush_all') {
            $response = Image_Functions::image_compress_all();
        }
    }

    public function wp_image_compression_progressbar() {
        global $wpdb;

        $images = array();
        $enabled_sizes = get_option('compression_sizes');
        if (!$enabled_sizes)
            $enabled_sizes = array('full');
        else
            $enabled_sizes[] = 'full';
        $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
        $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';

        $query = "select p.ID,p.guid,p.post_date,ca.saved,ca.is_history,cs.image_size,ca.action,cs.image_size_path,cs.image_file_size from
                    $wpdb->posts p
                    left join $crush_image_actions_table ca on p.ID = ca.image_id
                    left join $table_image_sizes cs on p.ID = cs.image_id
                    where p.post_type = 'attachment' and p.post_status = 'inherit' and
                    (p.post_mime_type = 'image/jpeg' OR p.post_mime_type = 'image/gif' OR p.post_mime_type = 'image/png')
                    and (ca.is_history = 0 OR ca.is_history Is Null) and (ca.action IN ('restored','error') OR ca.action Is Null)
                    and cs.image_size IN ('" . implode("', '", $enabled_sizes) . "') ";

        $crushed_images = $wpdb->get_results($query);
        if (!empty($crushed_images)) {
            foreach ($crushed_images as $crushed_image) {
                $id = $crushed_image->ID . '_' . $crushed_image->image_size;
                $images[$id] = $crushed_image->image_size_path;
            }
            count($images);
        }
        ?>

        <div class=" ui-progressbar ui-widget ui-widget-content ui-corner-all" id="upload_process" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        </div>
        <br>
        <div id="information" ></div>
        <?php
        $this->process($images);
    }

    public function process($images) {
        ob_start();
        $total = count($images);
        $keys = array_keys($images);
        $progress = get_option('image_crush_created', false);
        if ($progress == false) {
            $data = array('created' => 0,
            );
        } else {
            $data = array('created' => $progress,
            );
        }
        if ($total == $data['created']) {
            $this->progress_process($percent = "100 %", $data['created'], $total);
            delete_option('image_crush_created');
            return;
        } else {
            for ($i = $data['created']; $i < $total; $i++) {
                $percent = intval(($i + 1) / $total * 100) . "%";
                $cond = $this->do_something($keys[$i]);
                if ($cond) {
                    $this->progress_process($percent, $i + 1, $total);
                    update_option('image_crush_created', $i + 1);
                    sleep(1);

                    wp_ob_end_flush_all();
                    flush();
                    wp_cache_flush();
                }
            }
        }
        return;
    }

    public function do_something($image) {
        list($width, $height, $type, $attr) = getimagesize($image);
        if (isset($width)) {
            return true;
        }
    }

    public function progress_process($percent, $i, $total) {
        echo '<script>
    parent.document.getElementById("upload_process").innerHTML="<div style=\"width:' . $percent . ';background:#40ad6e; ;height:35px;\">&nbsp;</div>";

    parent.document.getElementById("information").innerHTML="<div style=\"text-align:center; font-weight:bold\">' . $percent . ' is processed ' . $i . ' is created from ' . $total . '</div>";'
        . '</script>';
    }

}
