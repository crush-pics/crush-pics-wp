<?php
//get user plan data
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

$compression_backup = get_option('compression_backup');
$has_restored = Image_Functions::is_action_exist($img_details->ID, 'full', 'restored');
$old_status = '';
if ($img_details->status == 'error' && $has_restored && !empty($compression_backup)) {
    $old_status = 'restored';
}
if (empty($img_details->saved)) {
    $img_details_saved = '0% ' . __('SAVED', 'wp-image-compression');
} else {
    $img_details_saved = ceil($img_details->saved) . '% ' . __('SAVED', 'wp-image-compression');
}
if ($img_details->status == 'restored' ):
    ?>
    <span class="badge badge-warning restored-status"><?php _e('RESTORED', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning crush-spinner" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Crushing', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning  restore-spinner mr-1" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Restoring', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning  already-crushed" style="display:none;">
        <?php _e('Already compressed', 'wp-image-compression'); ?>
    </span>
    <span class="badge badge-primary saved-image" style="display:none;"></span>
    <span class="badge badge-primary queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
    <span class="badge badge-secondary not-crushed-status" style="display:none;"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
<?php elseif ($img_details->status == 'crushed'): ?>
    <span class="badge badge-primary saved-image"><?php echo $img_details_saved; ?></span>
    <span class="badge badge-warning crush-spinner" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Crushing', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning  restore-spinner mr-1" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Restoring', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning  already-crushed" style="display:none;">
        <?php _e('Already compressed', 'wp-image-compression'); ?>
    </span>
    <span class="badge badge-secondary not-crushed-status" style="display:none;"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning restored-status" style="display:none;"><?php _e('RESTORED', 'wp-image-compression'); ?></span>
    <span class="badge badge-primary queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
<?php elseif ($img_details->status == 'error'): ?>
    <span class="badge badge-primary queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
    <span class="badge badge-secondary  not-crushed-status" style="display:none;"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning  already-crushed" >
        <?php _e('Already compressed', 'wp-image-compression'); ?>
    </span>
    <span class="badge badge-warning  crush-spinner" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Crushing', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning   restore-spinner mr-1" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Restoring', 'wp-image-compression'); ?></span>
    <span class="badge badge-success  crushed-status" style="display:none;"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
    <span class="badge badge-primary  saved-image" style="display:none;"></span>
    <span class="badge badge-warning  restored-status" style="display:none;"><?php _e('RESTORED', 'wp-image-compression'); ?></span>

<?php else: ?>
    <span class="badge badge-primary queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
    <span class="badge badge-secondary  not-crushed-status"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning  crush-spinner" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Crushing', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning   restore-spinner mr-1" style="display:none;">
        <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
        <?php _e('Restoring', 'wp-image-compression'); ?></span>
    <span class="badge badge-warning  already-crushed" style="display:none;">
        <?php _e('Already compressed', 'wp-image-compression'); ?>
    </span>
    <span class="badge badge-primary  saved-image" style="display:none;"></span>
    <span class="badge badge-warning  restored-status" style="display:none;"><?php _e('RESTORED', 'wp-image-compression'); ?></span>


<?php endif; ?>

<?php
$data_backup = '';

if (!empty($img_details->backup_image))
    $data_backup = $img_details->backup_image;

$full_size_media_image = wp_get_attachment_image_src( $img_details->ID, 'full' );


if (!empty($img_details->status) && $img_details->status == 'crushed' && !empty($data_backup) && !empty($compression_backup)):
    ?>

    <button class="btn btn-secondary btn-sm w-100 wp_image_compress main-card-container" style="display:none;" data-guid="" data-id="" data-size="full"  type="button" data-upgrade = <?php echo $upgrade_plan; ?>><?php _e('Crush', 'wp-image-compression'); ?></button>

    <button class="btn btn-secondary btn-sm w-100 wp_image_restore" data-backup="<?php echo $data_backup; ?>" data-guid="<?php echo $full_size_media_image[0]; ?>" data-id="<?php echo $img_details->ID; ?>" data-size="full" type="button" ><?php _e('Restore original', 'wp-image-compression'); ?></button>

<?php elseif (empty($img_details->status) || (!empty($img_details->status) && ($img_details->status == 'restored' || $img_details->status == 'error' || $img_details->status == 'enqueued'))): ?>

    <button class="btn btn-secondary btn-sm w-100 wp_image_compress main-card-container" data-guid="<?php echo $full_size_media_image[0]; ?>" data-id="<?php echo $img_details->ID; ?>" data-size="full" data-status="<?php echo $action = !empty($img_details->action) ? $img_details->action : ''; ?>"  type="button" data-upgrade = <?php echo $upgrade_plan; ?>><?php _e('Crush', 'wp-image-compression'); ?></button>

    <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" type="button" ><?php _e('Restore original', 'wp-image-compression'); ?></button>

<?php else: ?>

    <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" type="button" ><?php _e('Restore original', 'wp-image-compression'); ?></button>

    <button class="btn btn-secondary btn-sm w-100 wp_image_compress main-card-container" style="display:none;" data-guid="" data-id="" data-size="full"  type="button" data-upgrade = <?php echo $upgrade_plan; ?>><?php _e('Crush', 'wp-image-compression'); ?></button>

<?php endif; ?>


