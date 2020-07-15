<?php

class Update_Progress {

    public function __construct() {
        add_action('wp_image_compression_progressbar', array($this, 'wp_image_compression_progressbar'));
    }

    public function wp_image_compression_progressbar() {
        global $wpdb;

	    $images_with_sizes         = array();
        $enabled_sizes             = get_option( 'compression_sizes' );
	    $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
		$sql = "SELECT p.ID, pm.meta_value, p.guid, p.post_date, ca.saved, ca.is_history, ca.action
            FROM $wpdb->posts p
            LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
            LEFT JOIN $crush_image_actions_table ca ON p.ID = ca.image_id
            WHERE p.post_type = 'attachment' AND p.post_status = 'inherit' AND
            ( p.post_mime_type = 'image/jpeg' OR p.post_mime_type = 'image/gif' OR p.post_mime_type = 'image/png' )
            AND pm.meta_key = '_wp_attachment_metadata'
            AND ( ca.is_history = 0 OR ca.is_history IS NULL ) AND ( ca.action IN ( 'restored', 'error' ) OR ca.action IS NULL )";
        $images = $wpdb->get_results( $sql );
        if ( ! empty( $images ) ) {
	        foreach ( $images as $image ) {
		        $attachment_id   = $image->ID;
		        $image_with_size = $image;
		        if ( ! empty( $image->meta_value ) ) {
			        $data = unserialize( $image->meta_value );
			        if ( ! empty( $attachment_id ) && ! empty( $data ) ) {
				        $upload_dir = wp_upload_dir();

				        //full image path and size
				        $full_image_path      = $upload_dir['basedir'] . '/' . $data['file'];
				        $full_image_url       = $upload_dir['baseurl'] . '/' . $data['file'];
				        $full_image_file_size = filesize( $full_image_path );
				        $full_image_name      = basename( $full_image_path );

				        // general dir
				        $base_dir_path = str_replace( $full_image_name, '', $full_image_path );
				        $base_dir_url  = str_replace( $full_image_name, '', $full_image_url );

				        $image_with_size->image_size      = 'full';
				        $image_with_size->image_size_path = $full_image_url;
				        $image_with_size->image_file_size = $full_image_file_size;
				        $images_with_sizes[]              = $image_with_size;
				        if ( $enabled_sizes && ! empty( $data['sizes'] ) ) {
					        foreach ( $data['sizes'] as $key => $size ) {
						        if ( in_array( $key, $enabled_sizes ) ) {
							        //sized image path and size
							        $sized_file_path = $base_dir_path . $size['file'];
							        $sized_file_url  = $base_dir_url . $size['file'];
							        $sized_file_size = filesize( $sized_file_path );

							        $image_with_size->image_size      = $key;
							        $image_with_size->image_size_path = $sized_file_url;
							        $image_with_size->image_file_size = $sized_file_size;
							        $images_with_sizes[]              = $image_with_size;
						        }
					        }
				        }
                    }
		        }
	        }
        }

        $crushed_images = $images_with_sizes;
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
