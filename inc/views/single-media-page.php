<?php
$plan_data = get_option('wpic_plan_data');
$quota_usage = 0;
$bytes = 0;
if (!empty($plan_data['quota_usage']))
    $quota_usage = round($plan_data['quota_usage'] / 1000000, 2);

if (!empty($plan_data['bytes']))
    $bytes = round($plan_data['bytes'] / 1000000, 2);
$upgrade_plan = 'no';
if ($quota_usage >= $bytes) {
    $upgrade_plan = 'yes';
}
?>
<div class="misc-pub-section misc-pub-crush-img-head">
    <img src = "<?php echo$crush_image; ?>"><span class="img-name"><?php _e('Crush.pics', 'wp-image-compression'); ?></span>

    <?php
    $compression_backup = get_option('compression_backup');
    $has_restored = Image_Functions::is_action_exist($img_details->ID, 'full', 'restored');
    $old_status = $img_details->status;
    if ($img_details->status == 'error' && $has_restored && !empty($compression_backup)) {
        $old_status = 'restored';
    }
    if (empty($img_details->saved)) {
        $img_details_saved = '0% ' . __('SAVED', 'wp-image-compression');
    } else {
        $img_details_saved = ceil($img_details->saved) . '% ' . __('SAVED', 'wp-image-compression');
    }
    if ($img_details->status == 'restored' || ($img_details->status == 'error' && $has_restored && !empty($compression_backup) )):
        ?>
        <span class="single-image-spinner crush-spinner" style="display:none;"><img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/> <?php _e('Crushing', 'wp-image-compression'); ?></span>
        <span class="single-image-spinner restore-spinner mr-1" style="display:none;"><img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/><?php _e('Restoring', 'wp-image-compression'); ?></span>
        <span class="queued single-image-spinner" style="display:none;"><?php _e('Queued', 'wp-image-compression'); ?></span>
    <?php elseif ($img_details->status == 'crushed'): ?>
        <span class="single-image-spinner crush-spinner" style="display:none;"><img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/><?php _e('Crushing', 'wp-image-compression'); ?></span>
        <span class="single-image-spinner restore-spinner mr-1" style="display:none;"><img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/><?php _e('Restoring', 'wp-image-compression'); ?></span>
        <span class="queued single-image-spinner" style="display:none;"><?php _e('Queued', 'wp-image-compression'); ?></span>
    <?php else: ?>
        <span class="queued single-image-spinner" style="display:none;"><?php _e('Queued', 'wp-image-compression'); ?></span>
        <span class="single-image-spinner crush-spinner" style="display:none;"><img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/><?php _e('Crushing', 'wp-image-compression'); ?></span>
        <span class="single-image-spinner restore-spinner mr-1" style="display:none;"><img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/><?php _e('Restoring', 'wp-image-compression'); ?></span>

    <?php endif; ?>

    <?php
    $data_backup = '';

    if (!empty($img_details->backup_image))
        $data_backup = $img_details->backup_image;

    $full_size_media_image = wp_get_attachment_image_src( $img_details->ID, 'full' );


    if (!empty($img_details->status) && $img_details->status == 'crushed' && !empty($data_backup) && !empty($compression_backup)):
        ?>

        <button class="btn btn-secondary btn-sm w-100 wp_image_compress main-card-container" style="display:none;" data-guid="" data-id="" data-size="full"  type="button" data-upgrade = <?php echo $upgrade_plan; ?>><?php _e('Crush image', 'wp-image-compression'); ?></button>

        <button class="btn btn-secondary btn-sm w-100 wp_image_restore" data-backup="<?php echo $data_backup; ?>" data-guid="<?php echo $full_size_media_image[0]; ?>" data-id="<?php echo $img_details->ID; ?>" data-size="full" type="button" ><?php _e('Restore original', 'wp-image-compression'); ?></button>

    <?php elseif (empty($img_details->status) || (!empty($img_details->status) && ($img_details->status == 'restored' || $img_details->status == 'error' || $img_details->status == 'enqueued'))): ?>

        <button class="btn btn-secondary btn-sm w-100 wp_image_compress main-card-container" data-guid="<?php echo $full_size_media_image[0]; ?>" data-id="<?php echo $img_details->ID; ?>" data-size="full" data-status="<?php echo $action = !empty($img_details->action) ? $img_details->action : ''; ?>"  type="button" data-upgrade = <?php echo $upgrade_plan; ?>><?php _e('Crush image', 'wp-image-compression'); ?></button>

        <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" type="button" ><?php _e('Restore original', 'wp-image-compression'); ?></button>

    <?php else: ?>

        <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" type="button" ><?php _e('Restore original', 'wp-image-compression'); ?></button>

        <button class="btn btn-secondary btn-sm w-100 wp_image_compress main-card-container" style="display:none;" data-guid="" data-id="" data-size="full"  type="button" data-upgrade = <?php echo $upgrade_plan; ?>><?php _e('Crush image', 'wp-image-compression'); ?></button>

    <?php endif; ?>




</div>
<?php
$full_image_details = Image_Functions::get_sized_image_details($img_details->ID);
$original_size = $img_details->image_file_size;
if (empty($full_image_details['crushed_size'])) {
    $crushed_size = '-';
    $saved = '-';
} else {
    $crushed_size = Image_Functions::format_size_units($full_image_details['crushed_size']);
    $item_saved_value = round((int) $original_size - (int) $full_image_details['crushed_size']);
    $saved = Image_Functions::format_size_units($item_saved_value);
}
$compression_type = get_post_meta($img_details->ID, 'image_compression_type', true);
?>
<div class="misc-pub-section misc-pub-original-size" data-status = <?php echo $old_status; ?>>
    <span> <?php _e('Original Size', 'wp-image-compression'); ?> :</span> <strong class="strong-space"><?php echo Image_Functions::format_size_units($original_size); ?></strong>
</div>
<div class="misc-pub-section misc-pub-crushed-size">
    <span> <?php _e('Crushed Size', 'wp-image-compression'); ?> :</span> <strong class="crushed_size_value strong-space"><?php echo $crushed_size; ?></strong>
</div>
<div class="misc-pub-section misc-pub-saved">
    <span> <?php _e('Amount Saved', 'wp-image-compression'); ?> :</span><strong class="saved_value strong-space"><?php echo $saved; ?></strong>
</div>
<div class="misc-pub-section misc-pub-compression-type">
    <span> <?php _e('Compression Type', 'wp-image-compression'); ?> :</span><strong class="compression_type_value strong-space"><?php echo Image_Functions::get_compression_type_text($compression_type); ?></strong>
    <?php if (empty($img_details->status) || (!empty($img_details->status) && ($img_details->status == 'restored' || $img_details->status == 'error' || $img_details->status == 'enqueued'))) { ?>
    <?php } else { ?>

    <?php } ?>
</div>
