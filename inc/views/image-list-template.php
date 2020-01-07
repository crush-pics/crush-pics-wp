<!-- Start ims list -->

<ul class="list-group list-group-horizontal align-items-center" id="all-images-list">

    <li>

        <span class="text-muted mb-0 border-right display-3 pr-3">

            <?php _e('All Images', 'wp-image-compression'); ?>

        </span>

    </li>

    <li>

        <span class="border-right px-3 text-uppercase font-weight-bold h5">

            <span class="text-muted "><?php _e('TOTAL', 'wp-image-compression'); ?>:</span>

            <span class="total-images-no"> <?php echo number_format($images_count); ?> </span>

        </span>

    </li>

    <li>

        <span class="border-right px-3 text-uppercase font-weight-bold h5">

            <span class="text-muted "><?php _e('CRUSHED', 'wp-image-compression'); ?>: </span>

            <span class="crushed-images-no"> <?php echo number_format($crushed_images_count); ?> </span>

        </span>

    </li>

    <li>

        <span class="px-3 text-uppercase font-weight-bold h5">

            <span class="text-muted "><?php _e('NOT CRUSHED', 'wp-image-compression'); ?>: </span>

            <span class="un-crushed-images-no">  <?php echo number_format($images_count - $crushed_images_count); ?> </span>

        </span>

    </li>

</ul>

<!-- start table -->

<div class="row">

    <div class="col-md-12">

        <div class="card mw-100 py-0 px-0 card-top">

            <form method="get">

                <input type="hidden" name="page" value="crush-pics"/>

                <div class="px-3  pt-4">

                    <div class="input-group d-flex border rounded align-items-center mb-4">

                        <div class="input-group-prepend">

                            <span class="input-group-text border-0 bg-white text-muted p-0 px-2" id="search-addon">

                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>

                            </span>

                        </div>

                        <input type="text" class="form-control border-0 shadow-none p-2" name="image_term" value="<?php echo $image_term; ?>" id="image-src-filter" placeholder="Search by file name" aria-describedby="search-addon" aria-label="Search images">

                    </div>

                </div>

            </form>
            <?php if (!empty($images)) { ?>

                <div class="table-responsive-md main-table">

                    <table class="table mb-0 table-image-container">

                        <thead>

                            <tr>

                                <th><?php _e('File name', 'wp-image-compression'); ?></th>

                                <th class="compression-status-col">

                                    <span><?php _e('Compression', 'wp-image-compression'); ?></span>

                                </th>
                                <th class="image-actions"></th>

                            </tr>

                        </thead>

                        <tbody>
                            <?php
                            $compression_backup = get_option('compression_backup');

                            foreach ($images as $image) {
                                ?>

                                <?php
                                $image_items = Image_Functions::get_image_items($image->ID);
                                if (!empty($image_items) && count($image_items) > 0)
                                    $last_child_class = "";
                                else
                                    $last_child_class = "last-child-item";
                                $has_restored = Image_Functions::is_action_exist($image->ID, 'full', 'restored');

                                $old_status = $image->status;
                                $is_restored = '';
                                if ($image->status == 'error' && $has_restored && !empty($compression_backup)) {
                                    $old_status = 'restored';
                                }
                                if ($has_restored && !empty($compression_backup)) {

                                    $is_restored = 'yes';
                                }


                                if (empty($image->saved)) {

                                    $image_saved = '0% ' . __('SAVED', 'wp-image-compression');
                                } else {

                                    $image_saved = ceil($image->saved) . '% ' . __('SAVED', 'wp-image-compression');
                                }
                                ?>
                                <tr class="check_status parent-custom-style parent-details-banner-<?php echo $image->ID; ?>" data-id ="<?php echo $image->ID; ?>" data-status ="<?php echo $old_status; ?>" data-is_restored ="<?php echo $is_restored; ?>" data-size="full">

                                    <td>

                                        <?php
                                        $media_image = wp_get_attachment_image_src($image->ID, 'thumbnail');

                                        $full_size_media_image = wp_get_attachment_image_src( $image->ID, 'full' );
                                        // getting backup url
                                        $upload_dir = wp_upload_dir();
                                        $backup_image_url = $upload_dir['baseurl'] . '/crushed-backup/' . basename( $full_size_media_image[0] );

                                        if (!empty($media_image)) {

                                            $media_image_url = $media_image[0];
                                        } else {

                                            $media_image_url = 'https://appstaging.crush.pics/assets/icn-placeholder-c4f67b922e194a94c8a78078015f9a18c3569a8c674b6ad6af3c7a0f23a39e4b.svg';
                                        }

                                        $data_backup = '';

                                        if (!empty($image->backup_image))
                                            $data_backup = $image->backup_image;
                                        ?>

                                        <div class="d-flex flex-row align-items-center">

                                        <?php if ( ! empty( $image->status ) && $image->status == 'crushed' && $full_size_media_image && ! empty( $data_backup ) && ! empty( $compression_backup ) ):  ?>
                                            <a class="image-preview-link text-decoration-none" data-toggle="tooltip" data-placement="top" title="Open image preview" data-before="<?php echo $full_size_media_image[0]; ?>" data-after="<?php echo $backup_image_url; ?>" href="#">
                                                <img class="mr-2" src="<?php echo $media_image_url; ?>" width="37" height="37">
                                            </a>
                                        <?php else: ?>
                                            <a class="image-preview-link text-decoration-none" data-toggle="tooltip" data-placement="top" title="Open image preview" href="#" style="display:none;">
                                                <img class="mr-2" src="<?php echo $media_image_url; ?>" width="37" height="37">
                                            </a>
                                            <img class="mr-2 image-item" src="<?php echo $media_image_url; ?>" width="37" height="37">
                                        <?php endif; ?>

                                                <?php
                                                $full_image_name = wp_basename($image->guid);
                                                //($image->status == 'error' && $has_restored && !empty($compression_backup) )
                                                ?>

                                                <span class="text-muted align-middle dashboard-image-name"><?php echo $full_image_name; ?></span>

                                        </div>

                                    </td>
                                    <td class="align-middle compression-status-col">

                                        <?php if ($image->status == 'restored') { ?>
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
                                            <span class="badge badge-success crushed-status" style="display:none;"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                            <span class="badge badge-primary saved-image" style="display:none;"></span>
                                            <span class="badge badge-primary queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
                                            <span class="badge badge-secondary not-crushed-status" style="display:none;"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
                                        <?php } elseif ($image->status == 'crushed') { ?>
                                            <span class="badge badge-success crushed-status"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                            <span class="badge badge-primary saved-image"><?php echo $image_saved; ?></span>
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
                                        <?php } elseif ($image->status == 'error') { ?>
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
                                        <?php } else { ?>
                                            <span class="badge badge-primary queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
                                            <span class="badge badge-secondary  not-crushed-status"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
                                            <span class="badge badge-warning  already-crushed" style="display:none;">
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
                                        <?php } ?>

                                    </td>

                                    <td class="align-middle image-actions">

                                        <div class="justify-content-end d-flex image-actions-div" >

                                            <div class=" px-1 mb-0 my-lg-2 crush_action">

                                                <?php
                                                if (!empty($image->status) && $image->status == 'crushed' && !empty($data_backup) && !empty($compression_backup)) {
                                                    ?>

                                                    <button class="btn btn-secondary btn-sm w-100 wp_image_compress" style="display:none;" data-guid="" data-id="" data-size="full"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                    <button class="btn btn-secondary btn-sm w-100 wp_image_restore" data-backup="<?php echo $data_backup; ?>" data-guid="<?php echo $image->guid; ?>" data-id="<?php echo $image->ID; ?>" data-size="full" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                <?php } elseif (empty($image->status) || (!empty($image->status) && ($image->status == 'restored' || $image->status == 'error' || $image->status == 'enqueued'))) { ?>

                                                    <button class="btn btn-secondary btn-sm w-100 wp_image_compress" data-guid="<?php echo $image->guid; ?>" data-id="<?php echo $image->ID; ?>" data-size="full" data-status="<?php echo $action = !empty($image->action) ? $image->action : ''; ?>"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                    <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                <?php } else { ?>

                                                    <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                    <button class="btn btn-secondary btn-sm w-100 wp_image_compress" style="display:none;" data-guid="" data-id="" data-size="full"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                <?php } ?>

                                            </div>

                                            <div class=" px-1 mb-0 my-lg-2 crush_action">

                                                <button class="btn btn-secondary btn-sm w-100 image_details" data-size="full" data-id="<?php echo $image->ID; ?>"><?php _e('Details', 'wp-image-compression'); ?></button>

                                                <button class="btn btn-secondary btn-sm w-100 image_close" data-size="full" data-id="<?php echo $image->ID; ?>" style="display:none;"><?php _e('Close', 'wp-image-compression'); ?></button>

                                            </div>

                                        </div>


                                    </td>

                                </tr>

                                <?php
                                $full_image_details = Image_Functions::get_sized_image_details($image->ID);

                                $original_size = $image->image_file_size;


                                if (empty($full_image_details['crushed_size'])) {

                                    $crushed_size = '-';

                                    $saved = '-';
                                } else {

                                    $crushed_size = Image_Functions::format_size_units($full_image_details['crushed_size']);
                                    //$saved_value = (int) Image_Functions::size_units($original_size) - (int) Image_Functions::size_units($full_image_details['crushed_size']);
                                    $saved = Image_Functions::get_saved_size(Image_Functions::size_from_bytes($original_size), Image_Functions::size_from_bytes($full_image_details['crushed_size']));

                                    // $saved = Image_Functions::format_size_units($saved_value);
                                }
                                ?>
                                <!-- section details banner -->

                                <tr class=" image-child check_status parent-custom-style parent-details-banner-<?php echo $image->ID; ?> parent-details-banner-item-<?php echo $image->ID; ?> <?php echo $last_child_class; ?>" data-id ="<?php echo $image->ID; ?>" data-status ="<?php echo $old_status; ?>" data-is_restored ="<?php echo $is_restored; ?>" data-size="full" style="display:none;">

                                    <td colspan="3" class="py-1 px-0  bg-custom border-top-0">

                                        <div class="table-responsive-md border border-0 px-0 px-md-5 mx-0">
                                            <?php
                                            $crushed_no = Image_Functions::get_number_crushed_size_images($image->ID);
                                            $total_versions = Image_Functions::get_total_number_crushed_size_images($image->ID);
                                            ?>
                                            <div class="crush-image-details parent-details-info-<?php echo $image->ID; ?>" style="display:none;">
                                                <span class="info-bold-text">
                                                    <span class="crushed-no"><?php echo $crushed_no ?> </span> <?php _e(' of ', 'wp-image-compression') ?> <span class="total-version"> <?php echo $total_versions; ?> </span>
                                                    <?php _e(' Versions ', 'wp-image-compression'); ?>
                                                </span>
                                                <?php _e('of this image have been compressed', 'wp-image-compression'); ?>
                                            </div>
                                            <table class="table mb-0 bg-white rounded border crush_image_sizes">

                                                <tbody>
                                                    <tr class=" ">
                                                        <td>
                                                            <div class="d-flex flex-row align-items-center">

                                                                <img class="mr-2" src="<?php echo $media_image_url; ?>" width="37" height="37">
                                                                    <span class="align-middle dashboard-image-name custom-image-text"> <?php _e(' Originally uploaded image ', 'wp-image-compression'); ?></span>
                                                            </div>

                                                        </td>
                                                        <td class="align-middle compression-status-col">

                                                            <?php if ($image->status == 'restored') { ?>
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
                                                                <span class="badge badge-success crushed-status" style="display:none;"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                                                <span class="badge badge-primary saved-image" style="display:none;"></span>
                                                                <span class="badge badge-primary queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
                                                                <span class="badge badge-secondary not-crushed-status" style="display:none;"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
                                                            <?php } elseif ($image->status == 'crushed') { ?>
                                                                <span class="badge badge-success crushed-status"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                                                <span class="badge badge-primary saved-image"><?php echo $image_saved; ?></span>
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
                                                            <?php } elseif ($image->status == 'error') { ?>
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

                                                            <?php } else { ?>
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
                                                                <span class="badge badge-success  crushed-status" style="display:none;"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                                                <span class="badge badge-primary  saved-image" style="display:none;"></span>
                                                                <span class="badge badge-warning  restored-status" style="display:none;"><?php _e('RESTORED', 'wp-image-compression'); ?></span>


                                                            <?php } ?>

                                                        </td>

                                                        <td class="align-middle image-actions">

                                                            <div class="justify-content-end d-flex image-actions-div" >

                                                                <div class=" px-1 mb-0 my-lg-2 crush_action">

                                                                    <?php
                                                                    $data_backup = '';

                                                                    if (!empty($image->backup_image))
                                                                        $data_backup = $image->backup_image;


                                                                    if (!empty($image->status) && $image->status == 'crushed' && !empty($data_backup) && !empty($compression_backup)) {
                                                                        ?>

                                                                        <button class="btn btn-secondary btn-sm w-100 wp_image_compress" style="display:none;" data-guid="" data-id="" data-size="full"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                                        <button class="btn btn-secondary btn-sm w-100 wp_image_restore" data-backup="<?php echo $data_backup; ?>" data-guid="<?php echo $image->guid; ?>" data-id="<?php echo $image->ID; ?>" data-size="full" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                                    <?php } elseif (empty($image->status) || (!empty($image->status) && ($image->status == 'restored' || $image->status == 'error' || $image->status == 'enqueued'))) { ?>

                                                                        <button class="btn btn-secondary btn-sm w-100 wp_image_compress" data-guid="<?php echo $image->guid; ?>" data-id="<?php echo $image->ID; ?>" data-size="full" data-status="<?php echo $action = !empty($image->action) ? $image->action : ''; ?>"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                                        <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                                    <?php } else { ?>

                                                                        <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="" data-guid="" data-id="" data-size="full" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                                        <button class="btn btn-secondary btn-sm w-100 wp_image_compress" style="display:none;" data-guid="" data-id="" data-size="full"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                                    <?php } ?>

                                                                </div>

                                                                <div class=" px-1 mb-0 my-lg-2 crush_action">

                                                                    <button class="btn btn-secondary btn-sm w-100 image_details image-details-<?php echo $image->ID; ?>" data-size="banner-full" data-id="<?php echo $image->ID; ?>"><?php _e('Details', 'wp-image-compression'); ?></button>

                                                                    <button class="btn btn-secondary btn-sm w-100 image_close image-close-<?php echo $image->ID; ?>" data-size="banner-full" data-id="<?php echo $image->ID; ?>" style="display:none;"><?php _e('Close', 'wp-image-compression'); ?></button>

                                                                </div>

                                                            </div>
                                                        </td>

                                                    </tr>

                                                </tbody>

                                            </table>

                                        </div>

                                    </td>

                                </tr>

                                <!-- end section details banner -->

                                <tr class=" image-child image-child-details parent-details-<?php echo $image->ID; ?> <?php echo $last_child_class; ?>" style="display:none;">

                                    <td colspan="3" class="p-0">

                                        <div class="table-responsive-md px-md-5 px-2 mx-0 bg-custom">

                                            <div class="row pt-1 pb-1 mx-0">

                                                <div class="col-md-8 d-flex flex-column dashboard-image-comp-details">

                                                    <div class="bg-light border rounded-top py-2 px-custom">

                                                        <h3 class="mb-0 badge badge-secondary rounded border-0">

                                                            <?php _e('COMPRESSION DETAILS', 'wp-image-compression'); ?>

                                                        </h3>

                                                    </div>

                                                    <div class="card-deck mx-0">

                                                        <div class="card m-0 rounded-0 border-top-0 border-right-0 border-bottom-0  py-lg-3 py-2">

                                                            <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                <h2 class="card-title display-d original_size_value card-details-title"><?php
                                                                    echo Image_Functions::format_size_units($original_size);
                                                                    ?></h4>

                                                                    <p class="card-text card-details-text">

                                                                        <?php _e('Original size', 'wp-image-compression'); ?>

                                                                    </p>

                                                            </div>

                                                        </div>

                                                        <div class="card m-0 rounded-0 border-top-0 border-bottom-0  py-lg-3 py-2">

                                                            <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                <h2 class="card-title display-4 crushed_size_value card-details-title"><?php echo $crushed_size ?></h4>

                                                                    <p class="card-text card-details-text">

                                                                        <?php _e('Crushed size', 'wp-image-compression'); ?>

                                                                    </p>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div class="card-deck mx-0">

                                                        <div class="card m-0 crush_image_details_left border-right-0 py-lg-3 py-2">

                                                            <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                <h2 class="card-title display-d saved_value card-details-title"><?php echo $saved; ?></h4>

                                                                    <p class="card-text card-details-text">

                                                                        <?php _e('Saved', 'wp-image-compression'); ?>

                                                                    </p>

                                                            </div>

                                                        </div>

                                                        <div class="card m-0 crush_image_details_right card-compression-type py-lg-3 py-2">

                                                            <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                <h2 class="card-title display-4 compression_type_value card-details-title"><?php echo Image_Functions::get_compression_type_text(); ?></h4>

                                                                    <p class="card-text card-details-text">

                                                                        <?php _e('Compression type', 'wp-image-compression'); ?>

                                                                    </p>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-md-4 d-flex flex-column  dashboard-image-history">

                                                    <div class="bg-light border py-2 px-custom rounded-top">

                                                        <h3 class="border mb-0 p-2 badge badge-secondary rounded border-0">

                                                            <?php _e('HISTORY', 'wp-image-compression'); ?>

                                                        </h3>

                                                    </div>

                                                    <?php
                                                    $image_history = Image_Functions::get_sized_image_history($image->ID);
                                                    ?>

                                                    <div class="card-deck mx-0 h-100">

                                                        <div class="card m-0 crush_card_history border-top-0 p-0">

                                                            <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                <ul class="outline-round w-100 image_history_list">

                                                                    <?php
                                                                    if (!empty($image_history)) {

                                                                        foreach ($image_history as $key => $history) {

                                                                            if (!empty($history) && is_array($history)) {

                                                                                foreach ($history as $value) {
                                                                                    ?>

                                                                                    <li>

                                                                                        <span><?php echo $value; ?></span>

                                                                                        <span class="text-muted d-block"><?php echo $key; ?></span>

                                                                                    </li>

                                                                                    <?php
                                                                                }
                                                                            } else {
                                                                                ?>

                                                                                <li>

                                                                                    <span><?php echo $history; ?></span>

                                                                                    <span class="text-muted d-block"><?php echo $key; ?></span>

                                                                                </li>

                                                                                <?php
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>

                                                                </ul>

                                                            </div>

                                                        </div>


                                                    </div>

                                                </div>


                                            </div>


                                        </div>

                                    </td>


                                </tr>

                                <?php
                                if (!empty($image_items)) {
                                    $num_items = count($image_items);
                                    $i = 0;
                                    foreach ($image_items as $image_item) {
                                        if (++$i === $num_items)
                                            $last_child_class = "last-child-item";
                                        else
                                            $last_child_class = '';


                                        $item_has_restored = Image_Functions::is_action_exist($image_item->image_id, $image_item->image_size, 'restored');

                                        $old_status = $image->status;
                                        $is_restored = '';
                                        if ($image->status == 'error' && $item_has_restored && !empty($compression_backup)) {
                                            $old_status = 'restored';
                                        }
                                        if ($item_has_restored && !empty($compression_backup)) {

                                            $is_restored = 'yes';
                                        }


                                        $item_data = Image_Functions::get_sized_image_details($image_item->image_id, $image_item->image_size);

                                        if (empty($item_data['saved'])) {

                                            $image_item_saved = '0% ' . __('SAVED', 'wp-image-compression');
                                        } else {

                                            $image_item_saved = ceil($item_data['saved']) . '% ' . __('SAVED', 'wp-image-compression');
                                        }
                                        ?>

                                        <tr class=" image-child parent-item-<?php echo $image->ID; ?> <?php echo $last_child_class; ?>  check_status" data-size ="<?php echo $image_item->image_size; ?>" data-status =" <?php echo $old_status; ?>" data-is_restored = '<?php echo $is_restored; ?>' data-id ="<?php echo $image_item->image_id; ?>" style="display:none;">

                                            <td colspan="3" class="py-1 px-0  bg-custom border-top-0">

                                                <div class="table-responsive-md border border-0 px-0 px-md-5 mx-0">

                                                    <table class="table mb-0 bg-white rounded border crush_image_sizes">

                                                        <tbody>

                                                            <tr>

                                                                <td class=" border-0">

                                                                    <?php
                                                                    $media_image_item = wp_get_attachment_image_src($image_item->image_id, 'thumbnail');

                                                                    if (!empty($media_image_item)) {

                                                                        $media_image_item_url = $media_image_item[0];
                                                                    } else {

                                                                        $media_image_item_url = 'https://appstaging.crush.pics/assets/icn-placeholder-c4f67b922e194a94c8a78078015f9a18c3569a8c674b6ad6af3c7a0f23a39e4b.svg';
                                                                    }
                                                                    ?>

                                                                    <div class="d-flex flex-row align-items-center">

                                                                        <img class="mr-2" src="<?php echo $media_image_item_url; ?>" width="37" height="37">

                                                                            <?php
                                                                            $image_name_text = $image_item->image_size . ' ' . __('size', 'wp-image-compression');
                                                                            $image_name = basename($image_item->image_size_path);
                                                                            $image_pixel = Image_Functions::get_image_size_format($image_name);
                                                                            ?>

                                                                            <span class="align-middle dashboard-image-name custom-image-text"><?php echo str_replace('_', ' ', $image_name_text); ?>

                                                                                <span class="text-muted-custom text-uppercase">

                                                                                    <?php echo $image_pixel; ?>

                                                                                </span>

                                                                            </span>

                                                                    </div>

                                                                </td>
                                                                <td class="align-middle border-0 compression-status-col">

                                                                    <?php if ((!empty($item_data['action']) && $item_data['action'] == 'restored')) { ?>
                                                                        <span class="badge badge-primary  queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-warning  restored-status"><?php _e('RESTORED', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-warning  crush-spinner" style="display:none;">
                                                                            <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
                                                                            <?php _e('Crushing', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-warning   restore-spinner mr-1" style="display:none;">
                                                                            <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
                                                                            <?php _e('Restoring', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-warning  already-crushed" style="display:none;">
                                                                            <?php _e('Already compressed', 'wp-image-compression'); ?>
                                                                        </span>
                                                                        <span class="badge badge-success  crushed-status" style="display:none;"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-primary  saved-image" style="display:none;"></span>
                                                                        <span class="badge badge-secondary  not-crushed-status" style="display:none;"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
                                                                    <?php } elseif (!empty($item_data['action']) && $item_data['action'] == 'crushed') { ?>
                                                                        <span class="badge badge-primary  queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-success  crushed-status"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-primary  saved-image"><?php echo $image_item_saved; ?></span>
                                                                        <span class="badge badge-warning  crush-spinner" style="display:none;">
                                                                            <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
                                                                            <?php _e('Crushing', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-warning   restore-spinner mr-1" style="display:none;">
                                                                            <img src="<?php echo WPIC_URL . 'assets/img/preloader.gif'; ?>" class="tag-loader"/>
                                                                            <?php _e('Restoring', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-warning  already-crushed" style="display:none;">
                                                                            <?php _e('Already compressed', 'wp-image-compression'); ?>
                                                                        </span>
                                                                        <span class="badge badge-secondary  not-crushed-status" style="display:none;"><?php _e('NOT CRUSHED', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-warning  restored-status" style="display:none;"><?php _e('RESTORED', 'wp-image-compression'); ?></span>
                                                                    <?php } elseif (!empty($item_data['action']) && $item_data['action'] == 'error') { ?>
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

                                                                    <?php } else { ?>
                                                                        <span class="badge badge-primary  queued" style="display:none;"><?php _e('QUEUED', 'wp-image-compression'); ?></span>
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
                                                                        <span class="badge badge-success  crushed-status" style="display:none;"><?php _e('CRUSHED', 'wp-image-compression'); ?></span>
                                                                        <span class="badge badge-primary  saved-image" style="display:none;"></span>
                                                                        <span class="badge badge-warning  restored-status" style="display:none;"><?php _e('RESTORED', 'wp-image-compression'); ?></span>


                                                                    <?php } ?>

                                                                </td>

                                                                <td class="align-middle border-0 image-actions">

                                                                    <div class="justify-content-end d-flex image-actions-div  mx-0">

                                                                        <div class=" px-1 mb-0 my-lg-2 crush_action">

                                                                            <?php
                                                                            $image_item_data_backup = '';

                                                                            if (!empty($item_data['backup_image']))
                                                                                $image_item_data_backup = $item_data['backup_image'];


                                                                            if (!empty($item_data['action']) && $item_data['action'] == 'crushed' && !empty($image_item_data_backup) && !empty($compression_backup)) {
                                                                                ?>

                                                                                <button class="btn btn-secondary btn-sm w-100 wp_image_compress" style="display:none;" data-guid="<?php echo $image_item->image_size_path; ?>" data-id="<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                                                <button class="btn btn-secondary btn-sm w-100 wp_image_restore" data-backup="<?php echo $image_item_data_backup; ?>" data-guid="<?php echo $image_item->image_size_path; ?>" data-id="<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                                            <?php } elseif (empty($item_data['action']) || (!empty($item_data['action']) && ($item_data['action'] == 'restored' || $item_data['action'] == 'error' || $item_data['action'] == 'enqueued'))) { ?>


                                                                                <button class="btn btn-secondary btn-sm w-100 wp_image_compress" data-guid="<?php echo $image_item->image_size_path; ?>" data-id="<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>" data-status="<?php echo $action = !empty($item_data['action']) ? $item_data['action'] : ''; ?>"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                                                <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="<?php echo $image_item_data_backup; ?>" data-guid="<?php echo $image_item->image_size_path; ?>" data-id="<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                                            <?php } else { ?>

                                                                                <button class="btn btn-secondary btn-sm w-100 wp_image_restore" style="display:none;" data-backup="<?php echo $image_item_data_backup; ?>" data-guid="<?php echo $image_item->image_size_path; ?>" data-id="<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>" ><?php _e('Restore', 'wp-image-compression'); ?></button>

                                                                                <button class="btn btn-secondary btn-sm w-100 wp_image_compress" style="display:none;" data-guid="<?php echo $image_item->image_size_path; ?>" data-id="<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>"  type="button"><?php _e('Compress', 'wp-image-compression'); ?></button>

                                                                            <?php } ?>

                                                                        </div>

                                                                        <div class=" px-1 mb-0 my-lg-2 crush_action">

                                                                            <button class="btn btn-secondary btn-sm w-100 image_details image-details-<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>" data-id="<?php echo $image_item->image_size . '-' . $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>" ><?php _e('Details', 'wp-image-compression'); ?></button>

                                                                            <button class="btn btn-secondary btn-sm w-100 image_close image-close-<?php echo $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>" data-id="<?php echo $image_item->image_size . '-' . $image_item->image_id; ?>" data-size="<?php echo $image_item->image_size; ?>"  style="display:none;"><?php _e('Close', 'wp-image-compression'); ?></button>

                                                                        </div>

                                                                    </div>

                                                                </td>

                                                            </tr>

                                                        </tbody>

                                                    </table>

                                                </div>

                                            </td>

                                        </tr>
                                        <tr class=" image-child image-child-details item-details-<?php echo $image_item->image_size . '-' . $image_item->image_id; ?> item-details-container-<?php echo $image_item->image_id; ?> <?php echo $last_child_class; ?>" style="display:none;">

                                            <?php
                                            $item_original_size = Image_Functions::get_image_item_original_size($image_item->image_id, $image_item->image_size);

                                            if (empty($item_data['crushed_size'])) {

                                                $item_crushed_size = '-';

                                                $item_saved = '-';
                                            } else {

                                                $item_crushed_size = Image_Functions::format_size_units($item_data['crushed_size']);
                                                //$item_saved_value = (int) Image_Functions::size_units($item_original_size) - (int) Image_Functions::size_units($item_data['crushed_size']);
                                                $item_saved = Image_Functions::get_saved_size(Image_Functions::size_from_bytes($item_original_size), Image_Functions::size_from_bytes($item_data['crushed_size']));

                                                // $item_saved = Image_Functions::format_size_units($item_saved_value);
                                            }
                                            ?>

                                            <td colspan="3" class="p-0">

                                                <div class="table-responsive-md px-md-5 px-2 mx-0 bg-custom">

                                                    <div class="row pt-1 pb-1 mx-0">

                                                        <div class="col-md-8 d-flex flex-column dashboard-image-comp-details">

                                                            <div class="bg-light border rounded-top py-2 px-custom">

                                                                <h3 class="mb-0 p-2 badge badge-secondary rounded border-0">

                                                                    <?php _e('COMPRESSION DETAILS', 'wp-image-compression'); ?>

                                                                </h3>

                                                            </div>

                                                            <div class="card-deck mx-0">

                                                                <div class="card m-0 rounded-0 border-top-0 border-right-0 border-bottom-0  py-lg-3 py-2">

                                                                    <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                        <h2 class="card-title display-d original_size_value card-details-title"><?php
                                                                            echo Image_Functions::format_size_units($item_original_size);
                                                                            ?></h4>

                                                                            <p class="card-text card-details-text">

                                                                                <?php _e('Original size', 'wp-image-compression'); ?>

                                                                            </p>

                                                                    </div>

                                                                </div>

                                                                <div class="card m-0 rounded-0 border-top-0 border-bottom-0  py-lg-3 py-2">

                                                                    <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                        <h2 class="card-title display-4 crushed_size_value card-details-title"><?php echo $item_crushed_size ?></h4>

                                                                            <p class="card-text card-details-text">

                                                                                <?php _e('Crushed size', 'wp-image-compression'); ?>

                                                                            </p>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                            <div class="card-deck mx-0">

                                                                <div class="card m-0 crush_image_details_left border-right-0 py-lg-3 py-2 custom-raduis-left">

                                                                    <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                        <h2 class="card-title display-d saved_value card-details-title"><?php echo $item_saved ?></h4>

                                                                            <p class="card-text card-details-text">

                                                                                <?php _e('Saved', 'wp-image-compression'); ?>

                                                                            </p>

                                                                    </div>

                                                                </div>

                                                                <div class="card m-0 crush_image_details_right card-compression-type py-lg-3 py-2 custom-raduis-right">

                                                                    <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center">

                                                                        <h2 class="card-title display-4 compression_type_value card-details-title"><?php echo Image_Functions::get_compression_type_text(); ?></h4>

                                                                            <p class="card-text card-details-text">

                                                                                <?php _e('Compression type', 'wp-image-compression'); ?>

                                                                            </p>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                        </div>


                                                        <div class="col-md-4 d-flex flex-column dashboard-image-history">

                                                            <div class="bg-light border py-2 px-custom rounded-top">

                                                                <h3 class="border mb-0 p-2 badge badge-secondary rounded border-0">

                                                                    <?php _e('HISTORY', 'wp-image-compression'); ?>

                                                                </h3>

                                                            </div>

                                                            <?php
                                                            $item_image_history = Image_Functions::get_sized_image_history($image_item->image_id, $image_item->image_size);
                                                            ?>

                                                            <div class="card-deck mx-0 h-100">

                                                                <div class="card m-0 crush_card_history border-top-0 p-0">

                                                                    <div class="card-body d-flex flex-wrap  flex-column align-items-center justify-content-center p-1">

                                                                        <ul class="outline-round w-100 image_history_list">

                                                                            <?php
                                                                            if (!empty($item_image_history)) {

                                                                                foreach ($item_image_history as $key => $item_history) {

                                                                                    if (!empty($item_history) && is_array($item_history)) {

                                                                                        foreach ($item_history as $value) {
                                                                                            ?>

                                                                                            <li>

                                                                                                <span><?php echo $value; ?></span>

                                                                                                <span class="text-muted d-block"><?php echo $key; ?></span>

                                                                                            </li>

                                                                                            <?php
                                                                                        }
                                                                                    } else {
                                                                                        ?>

                                                                                        <li>

                                                                                            <span><?php echo $history; ?></span>

                                                                                            <span class="text-muted d-block"><?php echo $key; ?></span>

                                                                                        </li>

                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>

                                                                        </ul>

                                                                    </div>

                                                                </div>


                                                            </div>

                                                        </div>


                                                    </div>


                                                </div>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } ?>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>
            <?php } else { ?>
                <div class="no-img-result"><img src="<?php echo WPIC_URL . 'assets/img/empty-results.svg'; ?>"/></div>

                <div class="text-center display-2"><?php _e('No images found', 'wp-image-compression'); ?></div>
                <div class="pt-2 pb-4 text-center text-muted"><?php _e('Try Changing the search term', 'wp-image-compression'); ?></div>

            <?php } ?>

        </div>

    </div>
    <?php
    if (!empty($images)) {
        $no_of_items = $total - ($paged - 1) * $limit;
        $count_number = $limit;
        if ($no_of_items < 10)
            $count_number = $no_of_items;
        ?>

        <div class="w-100 bg-light mx-3">

            <p class="text-center my-3"><?php echo __('Showing ', 'wp-image-compression') . $count_number . __(' of ', 'wp-image-compression') . number_format_i18n($total) . __(' results', 'wp-image-compression'); ?></p>

        </div>

    <?php } ?>

    <div class="mt-4 pagination-with-preloader w-100 ">

        <nav class="d-flex align-items-center justify-content-center w-75 m-auto">

            <?php
            $current_url = admin_url('admin.php??page=crush-pics');
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%', $current_url),
                'format' => '',
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                'total' => $num_of_pages,
                'current' => $paged,
                'add_fragment' => '#all-images-list',
                'type' => 'array',
                'before_page_number' => '<span class="circle">',
                'after_page_number' => '</span>'
            ));

            $first_page_link = add_query_arg(array('paged' => 1), $current_url);

            $last_page_link = add_query_arg(array('paged' => $num_of_pages), $current_url);

            if (!empty($page_links)) {
                ?>

                <ul class="pagination justify-content-center">

                    <li class="page-item first">

                        <a class="page-link" href="<?php echo $first_page_link; ?>#all-images-list">

                            <?php _e('First', 'wp-image-compression'); ?> <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-skip-back"><polygon points="19 20 9 12 19 4 19 20"></polygon><line x1="5" y1="19" x2="5" y2="5"></line></svg>

                        </a>

                    </li>

                    <?php foreach ($page_links as $page_link) { ?>

                        <li class="page-item d-flex align-items-center">

                            <?php echo $page_link; ?>

                        </li>

                    <?php } ?>

                    <li class="page-item last next">

                        <a class="page-link" href="<?php echo $last_page_link; ?>#all-images-list">

                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-skip-forward"><polygon points="5 4 15 12 5 20 5 4"></polygon><line x1="19" y1="5" x2="19" y2="19"></line></svg> <?php _e('Last', 'wp-image-compression'); ?>

                        </a>

                    </li>

                </ul>

                <?php
            }
            ?>
        </nav>

    </div>


</div>

<!-- compare image preview -->
<div class="modal fade" tabindex="-1" role="dialog" id="image-preview-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
