<?php

if (!defined('ABSPATH'))
    exit;

class Media_List_View {

    public function __construct() {
        add_filter('manage_media_columns', array($this, 'wpic_media_list'));
        add_action('manage_media_custom_column', array($this, 'wpic_media_list_details'), 10, 2);
        add_action('admin_print_styles-upload.php', array($this, 'wpic_media_list_details_width'));
        add_action('admin_footer', array($this, 'wpic_media_footer_function'));
        add_action('attachment_submitbox_misc_actions', array($this, 'add_html_to_media_edit_page'), 99);
    }

    function add_html_to_media_edit_page() {

        global $wpdb, $post;
        if (!empty($post->ID)) {
            $post_id = $post->ID;
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
            $query = "select p.ID,p.guid,p.post_date,(select i.saved from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as saved
                ,(select i.image_backup_path from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as backup_image
                ,(select i.action from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as status
                ,(select i.image_file_size from $table_image_sizes i where p.ID = i.image_id and i.image_size = 'full') as image_file_size
                from $wpdb->posts p
                where
                p.ID = $post_id";
            $img_details = $wpdb->get_row($query);
            $crush_image = WPIC_URL . 'assets/img/crush-pics.svg';
            $loading_image = WPIC_URL . 'assets/img/loading.gif';
            require WPIC_PATH . 'inc/views/single-media-page.php';
        }
    }

    function add_custom_meta_box_single_image() {

        add_meta_box("wpic-media-details", "gggg", array($this, "wpic_media_crush_details"), "attachment", "side", "low");
    }

    function wpic_media_crush_details($post) {
        //check if img
        if (wp_attachment_is_image($post->ID)) {
            require WPIC_PATH . 'inc/views/single-media-page.php';
        }
    }

    function wpic_media_footer_function() {
        global $post;
        $screen = get_current_screen();
        if ($screen->id == 'upload' || ($screen->id == 'attachment' && !empty($post->ID) && wp_attachment_is_image($post->ID))) {
            $crush_webapp_url = "'" . Image_Compression_Menu::CRUSH_WEBAPP_URL . "'";
            echo '
              <div class="modal fade" id="general-error-status-msg" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">
              <div class="modal-dialog" role="document">
              <div class="modal-content">
              <div class="modal-header">
              <h5 class="modal-title" id="EditMoadlTitle">' . __('Error', 'wp-image-compression') . '</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div><div class="modal-body"></div></div></div></div>';
            echo '<div class="modal fade" id="upgrade_error" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                  <div class="modal-content"><div class="modal-header">
                  <h2 class="modal-title" id="EditMoadlTitle">' . __('Out of Quota', 'wp-image-compression') . '</h2>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                  </button></div><div class="modal-body">
                <p>' . __("Your quota for this month has run out. You’ll need to upgrade your plan to continue optimizing images.", 'wp-image-compression') . '</p>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">' . __('Cancel', 'wp-image-compression') . '</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.open(' . $crush_webapp_url . ');">' . __('Explore other plans', 'wp-image-compression') . '</button>
                </div>
                </div></div></div>';
        }
        if ($screen->id == 'attachment' && !empty($post->ID) && wp_attachment_is_image($post->ID)) {
            $compression_type = get_post_meta($post->ID, 'image_compression_type', true);
            if (empty($compression_type)) {
                $compression_type = get_option('compression_type', '-');
            }
            $balanced_check = ($compression_type == 'balanced') ? 'checked' : '';
            $lossless_check = ($compression_type == 'lossless') ? 'checked' : '';
            $lossy_check = ($compression_type == 'lossy') ? 'checked' : '';
            echo '<div class="modal fade" id="choose-compression-type" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title" id="EditMoadlTitle">' . __('Choose compression type', 'wp-image-compression') . '</h2>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body body">
                            <form>
                                <div class="custom-control custom-radio">
                                        <input class="custom-control-input" id="compression_balanced_input" type="radio" name="compression_type" value="balanced" ' . $balanced_check . ' >
                                        <label class="custom-control-label" for="compression_balanced_input">
                                            <span class="ml-2">' . __('Balanced', 'wp-image-compression') . '</span>
                                            <span class="radio-text">' . __('Reduce a lot of weight and some quality', 'wp-image-compression') . '</span>
                                        </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" id="compression_lossless_input" type="radio" name="compression_type" value="lossless" ' . $lossless_check . '>
                                         <label class="custom-control-label" for="compression_lossless_input">
                                            <span class="ml-2">' . __('Conservative', 'wp-image-compression') . '</span>
                                            <span class="radio-text">' . __('Reduce weight and not the quality', 'wp-image-compression') . '</span>
                                        </label>
                                </div>
                               <div class="custom-control custom-radio">
                                    <input class="custom-control-input" id="compression_lossy_input" type="radio" name="compression_type" value="lossy" ' . $lossy_check . '>
                                         <label class="custom-control-label" for="compression_lossy_input">
                                            <span class="ml-2">' . __('Custom', 'wp-image-compression') . '</span>
                                            <span class="radio-text">' . __('Define your own compression levels', 'wp-image-compression') . '</span>
                                        </label>
                                </div>
                                <form>
                                 <div class="note-container">
                                    <span class="note-text">' . __('Note', 'wp-image-compression') . ':</span>
                                    <span class="note-content">' . __('Compression type selected here is for this image only. This won’t affect your global compression settings.', 'wp-image-compression') . '</span>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">' . __('Close', 'wp-image-compression') . '</button>
                                <button type="button" class="btn btn-primary compression_type_save">' . __('Save changes', 'wp-image-compression') . '</button>
                            </div>
                        </div>
                    </div>
                  </div>';
        }
    }

    public function wpic_media_list($posts_columns) {
        $posts_columns['crushpics'] = __('Crushpics', 'wp-image-compression');

        return $posts_columns;
    }

    public function wpic_media_list_details($column_name, $post_id) {
        if ('crushpics' !== $column_name) {
            return;
        }
        global $wpdb;
        $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';

        //check if img
        if (wp_attachment_is_image($post_id)) {
            $crush_image_actions_table = $wpdb->prefix . 'crush_image_actions';
            $table_image_sizes = $wpdb->prefix . 'crush_image_sizes';
            $query = "select p.ID,p.guid,p.post_date,(select i.saved from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as saved
                ,(select i.image_backup_path from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as backup_image
                ,(select i.action from $crush_image_actions_table i where p.ID = i.image_id and i.is_history = 0 and i.image_size = 'full') as status
                ,(select i.image_file_size from $table_image_sizes i where p.ID = i.image_id and i.image_size = 'full') as image_file_size
                from $wpdb->posts p
                where
                p.ID = $post_id";
            $img_details = $wpdb->get_row($query);
            require WPIC_PATH . 'inc/views/media-list.php';
        } else {
            echo '-';
        }
    }

    public function wpic_media_list_details_width() {
        echo
        '<style>
		.fixed .column-crushpics {
			width: 100px;
                        padding-right: 32px;
                        color: #0073aa;
		}
	</style>';
    }

}
